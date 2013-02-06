
def sendData(data):
    # sock = socket(socket.AF_INET, socket.SOCK_STREAM)
    sock = socket()
    try:
          sock.connect( (CARBON_SERVER,CARBON_PORT) )
    except:
          print "Couldn't connect to %(server)s on port %(port)d, is carbon-agent.py running?" % { 'server':
                  CARBON_SERVER, 'port':CARBON_PORT }
          sys.exit(1)
    sock.sendall(data)
    sock.close()


if __name__ == "__main__":
    lines = doData()
    mesg = '\n'.join(lines) + '\n'
    # print "\nlines: %s" % (mesg)
    sendData(mesg)

