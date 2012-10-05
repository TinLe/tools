
def get():
    return getServerStatus()["indexCounters"]["btree"]

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
    for k,v in get().iteritems():
        lines.append("mongodb." + hostname + dc + ".btree.%s %s %d" % ( str(k), str(int(v)), now ))
    return lines

