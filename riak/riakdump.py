#!/usr/bin/env python

"""
Convert a MongoDB db into Riak K/V bucket

9/26/12 tin
"""

from argparse import ArgumentParser
import os
import riak
import json

def compute_signature(index):
    signature = index["ns"]
    for key in index["key"]:
        signature += "%s_%s" % (key, index["key"][key])
    return signature

def get_collection_stats(database, collection):
    print "Checking DB: %s" % collection.full_name
    return database.command("collstats", collection.name)

# From http://www.5dollarwhitebox.org/drupal/node/84
def convert_bytes(bytes):
    bytes = float(bytes)
    if bytes >= 1099511627776:
        terabytes = bytes / 1099511627776
        size = '%.2fT' % terabytes
    elif bytes >= 1073741824:
        gigabytes = bytes / 1073741824
        size = '%.2fG' % gigabytes
    elif bytes >= 1048576:
        megabytes = bytes / 1048576
        size = '%.2fM' % megabytes
    elif bytes >= 1024:
        kilobytes = bytes / 1024
        size = '%.2fK' % kilobytes
    else:
        size = '%.2fb' % bytes
    return size

def main():
    description = 'Generate size statistics for all collections in all DBs in MongoDB'

    global args
    parser = ArgumentParser(description=description)
    parser.add_argument('-H', '--host', default='wwwdev-a-1.qts.melodis.com',
      help="mongodb host, e.g. 'api.foo.com' default to 'localhost' if not specified")
    parser.add_argument('-P', '--port', type=int, default=8098,
      help="riak port if not the default 8098")
    parser.add_argument('-d', '--database', default='',
      help="database (default is all)")
    args = parser.parse_args()

    client = riak.RiakClient(host='wwwdev-a-1.qts.melodis.com', port=8098)
    bucket = client.bucket('restaurants')
    mykeys = bucket.get_keys()
    print "Number of keys: %d" % (len(mykeys))

    query = client.add('places')
    query.map("function(v) { var data = JSON.parse(v.values[0].data); if(data._id is not None) { return [[v.key, data]]; } return []; }")

    for result in query.run():
        print "%s - %s" % (result[0], result[1])

if __name__ == "__main__":
    main()
