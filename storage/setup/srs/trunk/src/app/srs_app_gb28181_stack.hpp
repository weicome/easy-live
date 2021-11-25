//
// Copyright (c) 2013-2021 Lixin
//
// SPDX-License-Identifier: MIT
//

#ifndef SRS_APP_GB28181_STACK_HPP
#define SRS_APP_GB28181_STACK_HPP

#include <srs_core.hpp>

#include <string>
#include <sstream>
#include <vector>
#include <map>

#include <srs_kernel_consts.hpp>
#include <srs_rtsp_stack.hpp>

class SrsBuffer;
class SrsSimpleStream;
class SrsAudioFrame;

// SIP methods
#define SRS_SIP_METHOD_REGISTER       "REGISTER"
#define SRS_SIP_METHOD_MESSAGE        "MESSAGE"
#define SRS_SIP_METHOD_INVITE         "INVITE"
#define SRS_SIP_METHOD_ACK            "ACK"
#define SRS_SIP_METHOD_BYE            "BYE"

// SIP-Version
#define SRS_SIP_VERSION "SIP/2.0"
#define SRS_SIP_USER_AGENT RTMP_SIG_SRS_SERVER

#define SRS_SIP_PTZ_START 0xA5


enum SrsSipCmdType{
    SrsSipCmdRequest=0,
    SrsSipCmdRespone=1
};

enum SrsSipPtzCmdType{
    SrsSipPtzCmdStop = 0x00,
    SrsSipPtzCmdRight = 0x01,
    SrsSipPtzCmdLeft  = 0x02,
    SrsSipPtzCmdDown  = 0x04,
    SrsSipPtzCmdUp    = 0x08,
    SrsSipPtzCmdZoomIn  = 0x10,
    SrsSipPtzCmdZoomOut   = 0x20
};

std::string srs_sip_get_utc_date();

class SrsSipRequest
{
public:
    //sip header member
    std::string method;
    std::string uri;
    std::string version;
    std::string status;

    std::string via;
    std::string from;
    std::string to;
    std::string from_tag;
    std::string to_tag;
    std::string branch;
    
    std::string call_id;
    long seq;

    std::string contact;
    std::string user_agent;

    std::string content_type;
    long content_length;

    long expires;
    int max_forwards;

    std::string www_authenticate;
    std::string authorization;

    std::string chid;

    std::map<std::string, std::string> xml_body_map;
    std::map<std::string, std::string> device_list_map;
    // add an item_list, you can do a lot of other things
    // used by DeviceList, Alarmstatus, RecordList in "GB/T 28181—2016"
    std::vector<std::map<std::string, std::string> > item_list;

public:
    std::string serial;
    std::string realm;
    std::string sip_auth_id;
    std::string sip_auth_pwd;
    std::string sip_username;
    std::string peer_ip;
    int peer_port;
    std::string host;
    int host_port;
    SrsSipCmdType cmdtype;

    std::string from_realm;
    std::string to_realm;
    uint32_t y_ssrc;

public:
    SrsRtspSdp* sdp;
    SrsRtspTransport* transport;
public:
    SrsSipRequest();
    virtual ~SrsSipRequest();
public:
    virtual bool is_register();
    virtual bool is_invite();
    virtual bool is_message();
    virtual bool is_ack();
    virtual bool is_bye();
   
    virtual void copy(SrsSipRequest* src);
public:
    virtual std::string get_cmdtype_str();
};

// The gb28181 sip protocol stack.
class SrsSipStack
{
private:
    // The cached bytes buffer.
    SrsSimpleStream* buf;
public:
    SrsSipStack();
    virtual ~SrsSipStack();
public:
    virtual srs_error_t parse_request(SrsSipRequest** preq, const char *recv_msg, int nb_buf);
protected:
    virtual srs_error_t do_parse_request(SrsSipRequest* req, const char *recv_msg);
    virtual srs_error_t parse_xml(std::string xml_msg, std::map<std::string, std::string> &json_map, std::vector<std::map<std::string, std::string> > &item_list);

private:
    //response from
    virtual std::string get_sip_from(SrsSipRequest const *req);
    //response to
    virtual std::string get_sip_to(SrsSipRequest const *req);
    //response via
    virtual std::string get_sip_via(SrsSipRequest const *req);

public:
    //response:  request sent by the sip-agent, wait for sip-server response
    virtual void resp_status(std::stringstream& ss, SrsSipRequest *req);
    virtual void resp_keepalive(std::stringstream& ss, SrsSipRequest *req);
  
    //request:  request sent by the sip-server, wait for sip-agent response
    virtual void req_invite(std::stringstream& ss, SrsSipRequest *req, std::string ip, 
        int port, uint32_t ssrc, bool tcpFlag);
    virtual void req_ack(std::stringstream& ss, SrsSipRequest *req);
    virtual void req_bye(std::stringstream& ss, SrsSipRequest *req);
    virtual void req_401_unauthorized(std::stringstream& ss, SrsSipRequest *req);
    virtual void req_query_catalog(std::stringstream& ss, SrsSipRequest *req);
    virtual void req_ptz(std::stringstream& ss, SrsSipRequest *req, uint8_t cmd, uint8_t speed,  int priority);
   
};

#endif

