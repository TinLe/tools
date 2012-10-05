
name = "locked"

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
    # lines.append("mongodb." + hostname + ".lock.%s %s %d" % ( name, str( 100 * getServerStatus()["globalLock"]["ratio"] ), now ))
    lines.append("mongodb." + hostname + dc + ".lock.totalTime.%s %s %d" % ( name, str( 100 * getServerStatus()["globalLock"]["totalTime"] ), now ))
    lines.append("mongodb." + hostname + dc + ".lock.lockTime.%s %s %d" % ( name, str( 100 * getServerStatus()["globalLock"]["lockTime"] ), now ))
    return lines

