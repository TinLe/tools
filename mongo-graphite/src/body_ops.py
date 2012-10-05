
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
    ss = getServerStatus()
    for k,v in ss["opcounters"].iteritems():
        lines.append("mongodb." + hostname + dc + ".ops.%s %s %d" % (str(k), str(v), now))
    return lines

