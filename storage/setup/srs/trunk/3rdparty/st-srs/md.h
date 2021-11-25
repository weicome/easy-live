/* SPDX-License-Identifier: MPL-1.1 OR GPL-2.0-or-later */

/*
 * The contents of this file are subject to the Mozilla Public
 * License Version 1.1 (the "License"); you may not use this file
 * except in compliance with the License. You may obtain a copy of
 * the License at http://www.mozilla.org/MPL/
 * 
 * Software distributed under the License is distributed on an "AS
 * IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * rights and limitations under the License.
 * 
 * The Original Code is the Netscape Portable Runtime library.
 * 
 * The Initial Developer of the Original Code is Netscape
 * Communications Corporation.  Portions created by Netscape are 
 * Copyright (C) 1994-2000 Netscape Communications Corporation.  All
 * Rights Reserved.
 * 
 * Contributor(s):  Silicon Graphics, Inc.
 * 
 * Portions created by SGI are Copyright (C) 2000-2001 Silicon
 * Graphics, Inc.  All Rights Reserved.
 * 
 * Alternatively, the contents of this file may be used under the
 * terms of the GNU General Public License Version 2 or later (the
 * "GPL"), in which case the provisions of the GPL are applicable 
 * instead of those above.  If you wish to allow use of your 
 * version of this file only under the terms of the GPL and not to
 * allow others to use your version of this file under the MPL,
 * indicate your decision by deleting the provisions above and
 * replace them with the notice and other provisions required by
 * the GPL.  If you do not delete the provisions above, a recipient
 * may use your version of this file under either the MPL or the
 * GPL.
 */

/*
 * This file is derived directly from Netscape Communications Corporation,
 * and consists of extensive modifications made during the year(s) 1999-2000.
 */

#ifndef __ST_MD_H__
#define __ST_MD_H__

#if defined(ETIMEDOUT) && !defined(ETIME)
    #define ETIME ETIMEDOUT
#endif

#if defined(MAP_ANONYMOUS) && !defined(MAP_ANON)
    #define MAP_ANON MAP_ANONYMOUS
#endif

#ifndef MAP_FAILED
    #define MAP_FAILED -1
#endif

/*****************************************
 * Platform specifics
 */

#if defined (DARWIN)

    #define MD_USE_BSD_ANON_MMAP
    #define MD_ACCEPT_NB_INHERITED
    #define MD_HAVE_SOCKLEN_T

    #define MD_USE_BUILTIN_SETJMP

    #if defined(__amd64__) || defined(__x86_64__)
        #define JB_SP  12 /* The jmpbuf is int(4B) array, while MD_GET_SP covert to long(8B) pointer, so the JB_SP should be 12 which is 6*sizeof(long)/sizeof(int) */
        #define MD_GET_SP(_t) *((long *)&((_t)->context[JB_SP]))
    #else
        #error Unknown CPU architecture
    #endif
    
    #define MD_INIT_CONTEXT(_thread, _sp, _main) \
        ST_BEGIN_MACRO                             \
        if (MD_SETJMP((_thread)->context))         \
            _main();                                 \
        MD_GET_SP(_thread) = (long) (_sp);         \
        ST_END_MACRO

    #if defined(MD_USE_BUILTIN_SETJMP)
        #define MD_SETJMP(env) _st_md_cxt_save(env)
        #define MD_LONGJMP(env, val) _st_md_cxt_restore(env, val)

        extern int _st_md_cxt_save(jmp_buf env);
        extern void _st_md_cxt_restore(jmp_buf env, int val);
    #endif

    #define MD_GET_UTIME()            \
        struct timeval tv;              \
        (void) gettimeofday(&tv, NULL); \
        return (tv.tv_sec * 1000000LL + tv.tv_usec)

#elif defined (LINUX)

    /*
     * These are properties of the linux kernel and are the same on every
     * flavor and architecture.
     */
    #define MD_USE_BSD_ANON_MMAP
    #define MD_ACCEPT_NB_NOT_INHERITED
    /*
     * Modern GNU/Linux is Posix.1g compliant.
     */
    #define MD_HAVE_SOCKLEN_T

    /*
     * All architectures and flavors of linux have the gettimeofday
     * function but if you know of a faster way, use it.
     */
    #define MD_GET_UTIME()            \
        struct timeval tv;              \
        (void) gettimeofday(&tv, NULL); \
        return (tv.tv_sec * 1000000LL + tv.tv_usec)

    #if 1

        /*
         * On linux, there are a few styles of jmpbuf format.  These vary based
         * on architecture/glibc combination.
         *
         * Most of the glibc based toggles were lifted from:
         * mozilla/nsprpub/pr/include/md/_linux.h
         */

        /*
         * Starting with glibc 2.4, JB_SP definitions are not public anymore.
         * They, however, can still be found in glibc source tree in
         * architecture-specific "jmpbuf-offsets.h" files.
         * Most importantly, the content of jmp_buf is mangled by setjmp to make
         * it completely opaque (the mangling can be disabled by setting the
         * LD_POINTER_GUARD environment variable before application execution).
         * Therefore we will use built-in _st_md_cxt_save/_st_md_cxt_restore
         * functions as a setjmp/longjmp replacement wherever they are available
         * unless USE_LIBC_SETJMP is defined.
         */

        #if defined(__i386__)
            #define MD_USE_BUILTIN_SETJMP

            #if defined(__GLIBC__) && __GLIBC__ >= 2
                #ifndef JB_SP
                    #define JB_SP 4
                #endif
                #define MD_GET_SP(_t) (_t)->context[0].__jmpbuf[JB_SP]
            #else
                /* not an error but certainly cause for caution */
                #error "Untested use of old glibc on i386"
                #define MD_GET_SP(_t) (_t)->context[0].__jmpbuf[0].__sp
            #endif

        #elif defined(__amd64__) || defined(__x86_64__)
            #define MD_USE_BUILTIN_SETJMP

            #ifndef JB_RSP
                #define JB_RSP 6
            #endif
            #define MD_GET_SP(_t) (_t)->context[0].__jmpbuf[JB_RSP]

        #elif defined(__aarch64__)
            /* https://github.com/ossrs/state-threads/issues/9 */
            #define MD_USE_BUILTIN_SETJMP
            #define MD_GET_SP(_t) (_t)->context[0].__jmpbuf[13]

        #elif defined(__arm__)
            /* https://github.com/ossrs/state-threads/issues/1#issuecomment-244648573 */
            #define MD_USE_BUILTIN_SETJMP

            /* force to use glibc solution, hack the guard jmpbuf from michaeltalyansky */
            #ifdef USE_LIBC_SETJMP
                #undef MD_USE_BUILTIN_SETJMP
            #endif

            #if defined(__GLIBC__) && __GLIBC__ >= 2
                /* Merge from https://github.com/michaeltalyansky/state-threads/commit/56554a5c425aee8e7a73782eae23d74d83c4120a */
                #define MD_GET_SP(_t) (_t)->context[0].__jmpbuf[8]
            #else
                #error "ARM/Linux pre-glibc2 not supported yet"
            #endif /* defined(__GLIBC__) && __GLIBC__ >= 2 */

        #elif defined(__mips__)
            /* https://github.com/ossrs/state-threads/issues/21 */
            #define MD_USE_BUILTIN_SETJMP
            #define MD_GET_SP(_t) *((long *)&((_t)->context[0].__jb[0]))

        #elif defined(__loongarch__)
            /* https://github.com/ossrs/state-threads/issues/24 */
            #define MD_USE_BUILTIN_SETJMP
            #define MD_GET_SP(_t) *((long *)&((_t)->context[0].__jmpbuf[0]))

        #else
            #error "Unknown CPU architecture"
        #endif /* Cases with common MD_INIT_CONTEXT and different SP locations */

        #define MD_INIT_CONTEXT(_thread, _sp, _main) \
            ST_BEGIN_MACRO                             \
            if (MD_SETJMP((_thread)->context))         \
                _main();                                 \
            MD_GET_SP(_thread) = (long) (_sp);         \
            ST_END_MACRO

    #endif /* Cases with different MD_INIT_CONTEXT */

    #if defined(MD_USE_BUILTIN_SETJMP) && !defined(USE_LIBC_SETJMP)
        #define MD_SETJMP(env) _st_md_cxt_save(env)
        #define MD_LONGJMP(env, val) _st_md_cxt_restore(env, val)

        extern int _st_md_cxt_save(jmp_buf env);
        extern void _st_md_cxt_restore(jmp_buf env, int val);
    #else
        #define MD_SETJMP(env) setjmp(env)
        #define MD_LONGJMP(env, val) longjmp(env, val)
    #endif

#elif defined (CYGWIN64)

    // For CYGWIN64, build SRS on Windows.
    #define MD_USE_BSD_ANON_MMAP
    #define MD_ACCEPT_NB_INHERITED
    #define MD_HAVE_SOCKLEN_T

    #define MD_USE_BUILTIN_SETJMP

    #if defined(__amd64__) || defined(__x86_64__)
        #define JB_SP  6 // The context is long(32) array, @see https://github.com/ossrs/state-threads/issues/20#issuecomment-887569093
        #define MD_GET_SP(_t) *((long *)&((_t)->context[JB_SP]))
    #else
        #error Unknown CPU architecture
    #endif

    #define MD_INIT_CONTEXT(_thread, _sp, _main) \
        ST_BEGIN_MACRO                             \
        if (MD_SETJMP((_thread)->context))         \
            _main();                                 \
        MD_GET_SP(_thread) = (long) (_sp);         \
        ST_END_MACRO

    #if defined(MD_USE_BUILTIN_SETJMP)
        #define MD_SETJMP(env) _st_md_cxt_save(env)
        #define MD_LONGJMP(env, val) _st_md_cxt_restore(env, val)

        extern int _st_md_cxt_save(jmp_buf env);
        extern void _st_md_cxt_restore(jmp_buf env, int val);
    #endif

    #define MD_GET_UTIME()            \
        struct timeval tv;              \
        (void) gettimeofday(&tv, NULL); \
        return (tv.tv_sec * 1000000LL + tv.tv_usec)

#else
    #error Unknown OS
#endif /* OS */

#ifndef MD_STACK_PAD_SIZE
    #define MD_STACK_PAD_SIZE 128
#endif

#if !defined(MD_HAVE_SOCKLEN_T) && !defined(socklen_t)
    #define socklen_t int
#endif

#ifndef MD_CAP_STACK
    #define MD_CAP_STACK(var_addr)
#endif

#endif /* !__ST_MD_H__ */

