
name = "connections"

def doData():
    lines = []
    m = re.search('^[^.]+', os.environ.get("host", "127.0.0.1"))
    hostname = m.group(0)
    dc = os.environ.get("dc")
    if dc is not None:
        dc = "." + dc
    else:
        dc = ""
    now = int( time.time() )
    # print name + ".value " + str( getServerStatus()["connections"]["current"] )
    lines.append("mongodb." + hostname + dc + ".conn.%s %s %d" % (name, str(getServerStatus()["connections"]["current"]), now))
    return lines


