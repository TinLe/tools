
Graphite monitoring for MongoDB
============

Modified from the Munin plugins for MongoDB by Eliot (https://github.com/erh/mongo-munin)

I put them in my crontab like so

*/5 * * * * host=foo.bar.baz.com dc=bar mongo_btree

Where host is the FQDN of the mongodb server I want to monitor and dc is the data center
it is in.

My graphite storage-schema.conf is something like this:

{

[mongodb]
pattern = ^mongodb\.
retentions = 60:30d,900:365d

}


so I have:

{

mongodb.foo.bar.btree.accesses
mongodb.foo.bar.btree.hits
mongodb.foo.bar.btree.missRatio
mongodb.foo.bar.btree.misses
mongodb.foo.bar.btree.resets

}


Monitoring scripts
----------
* mongo_ops   : operations/second
* mongo_mem   : mapped, virtual and resident memory usage
* mongo_btree : btree access/misses/etc...
* mongo_conn  : current connections
* mongo_lock  : write lock info  

Requirements
-----------
* simplejson or python >= 2.6
* MongoDB 1.4+ 



