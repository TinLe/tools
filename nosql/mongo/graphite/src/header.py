
import urllib2
import sys
import time
import os
import re
from socket import socket

# graphite-a-1 (10.0.0.30)
CARBON_SERVER = '127.0.0.1'
CARBON_PORT = 2003

try:
    import json
except ImportError:
    import simplejson as json


def getServerStatus():
    host = os.environ.get("host", "127.0.0.1")
    port = 28017
    url = "http://%s:%d/_status" % (host, port)
    try:
      req = urllib2.Request(url)
    except:
      print "Couldn't connect to %(url)s on %(host)s, port %(port)d, is mongodb running?" % { 'url':url, 'host':host, 'port':port }
      sys.exit(1)
    user = os.environ.get("user")
    password = os.environ.get("password")
    if user and password:
        passwdmngr = urllib2.HTTPPasswordMgrWithDefaultRealm()
        passwdmngr.add_password(None, 'http://%s:%d' % (host, port), user, password)
        authhandler = urllib2.HTTPDigestAuthHandler(passwdmngr)
        opener = urllib2.build_opener(authhandler)
        urllib2.install_opener(opener)
    try:
      raw = urllib2.urlopen(req).read()
    except:
      print "Couldn't read to %(url)s on %(host)s, port %(port)d, is mongodb running?" % { 'url':url, 'host':host, 'port':port }
      sys.exit(1)
    return json.loads( raw )["serverStatus"]

