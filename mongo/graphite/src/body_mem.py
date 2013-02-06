
def ok(s):
    return s == "resident" or s == "virtual" or s == "mapped"

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
    for k,v in getServerStatus()["mem"].iteritems():
        if ok(k):
            lines.append("mongodb." + hostname + dc + ".mem.%s %s %d" % (str(k), str(v*1024*1024), now))
    return lines
