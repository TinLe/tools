#!/usr/bin/env python
import os
import sys
import time
import json
import requests
import logging
from datetime import timedelta, datetime, date

try:
    from logging import NullHandler
except ImportError:
    from logging import Handler

    class NullHandler(Handler):
        def emit(self, record):
            pass

__version__ = '1.0.0-dev'

logger = logging.getLogger(__name__)

DEFAULT_ARGS = {
    'host': 'localhost',
    'url_prefix': '',
    'port': 9200,
    'auth': None,
    'ssl': False,
    'timeout': 30,
    'prefix': 'logstash-',
    'index': 'logstash-',
    'suffix': '',
    'time_unit': 'days',
    'dry_run': False,
    'log_level': 'INFO',
    'logformat': 'Default',
    'debug': False,
    'output': '-',
    'query': '{"query":{"query_string":{"query":"*"}}}',
}

DATEMAP = {
    'months': '%Y.%m',
    'weeks': '%Y.%W',
    'days': '%Y.%m.%d',
    'hours': '%Y.%m.%d.%H',
}


def add_common_args(subparser):
    """Add common arguments here to reduce redundancy and line count"""
    subparser.add_argument('--timestring', help="Python strftime string to match your index definition, e.g. 2014.07.15 would be %%Y.%%m.%%d", type=str, default=None)
    subparser.add_argument('--prefix', help='Define a prefix. Index name = PREFIX + TIMESTRING + SUFFIX. Default: logstash-', default=DEFAULT_ARGS['prefix'])
    subparser.add_argument('--suffix', help='Define a suffix. Index name = PREFIX + TIMESTRING + SUFFIX. Default: Empty', default=DEFAULT_ARGS['suffix'])
    subparser.add_argument('--time-unit', dest='time_unit', action='store', help='Unit of time to reckon by: [hours|days|weeks|months] Default: days', default=DEFAULT_ARGS['time_unit'], type=str)

def make_parser():
    """ Creates an ArgumentParser to parse the command line options. """
    help_desc = 'Query Dumper for Elasticsearch indices.'
    try:
        import argparse
        parser = argparse.ArgumentParser(description=help_desc)
        parser.add_argument('-v', '--version', action='version', version='%(prog)s '+__version__)
    except ImportError:
        print('{0} requires module argparse.  Try: pip install argparse'.format(sys.argv[0]))
        sys.exit(1)

    # Common args
    parser.add_argument('--host', help='Elasticsearch host. Default: localhost', default=DEFAULT_ARGS['host'])
    parser.add_argument('--url_prefix', help='Elasticsearch http url prefix. Default: none', default=DEFAULT_ARGS['url_prefix'])
    parser.add_argument('--port', help='Elasticsearch port. Default: 9200', default=DEFAULT_ARGS['port'], type=int)
    parser.add_argument('--ssl', help='Connect to Elasticsearch through SSL. Default: false', action='store_true', default=DEFAULT_ARGS['ssl'])
    parser.add_argument('--auth', help='Use Basic Authentication ex: user:pass Default: None', default=DEFAULT_ARGS['auth'])
    parser.add_argument('--timeout', help='Connection timeout in seconds. Default: 30', default=DEFAULT_ARGS['timeout'], type=int)
    parser.add_argument('-n', '--dry-run', action='store_true', help='If true, does not perform any changes to the Elasticsearch indices.', default=DEFAULT_ARGS['dry_run'])
    parser.add_argument('-D', '--debug', dest='debug', action='store_true', help='Debug mode', default=DEFAULT_ARGS['debug'])
    parser.add_argument('--loglevel', dest='log_level', action='store', help='Log level', default=DEFAULT_ARGS['log_level'], type=str)
    parser.add_argument('--logfile', dest='log_file', help='log file', type=str)
    parser.add_argument('--logformat', dest='logformat', help='Log output format [default|logstash]. Default: default', default=DEFAULT_ARGS['logformat'], type=str)

    # Command sub_parsers
    subparsers = parser.add_subparsers(
            title='Commands', dest='command', description='Select one of the following commands:',
            help='Run: ' + sys.argv[0] + ' COMMAND --help for command-specific help.')

    # Dump Query
    parser_dump_query = subparsers.add_parser('query_dump', help='Dump Query output')
    parser_dump_query.set_defaults(func=dump_query)
    add_common_args(parser_dump_query)
    parser_dump_query.add_argument('--index', help='ES index to dump data from. Default: ', default=DEFAULT_ARGS['index'])
    parser_dump_query.add_argument('--query', help='Define an ES query in json format. Default: "*"', default=DEFAULT_ARGS['query'])
    parser_dump_query.add_argument('--out', help='Output for dump. Default: stdout', type=argparse.FileType('w'), default=DEFAULT_ARGS['output'])

    return parser

'''
* Dump it!
'''
def dump_query(**kwargs):
    url = "http://" + kwargs['host'] + ":" + "{0}".format(kwargs['port']) + "/" + kwargs['index'] + "/_search?scroll=1m&search_type=scan"
    try:
      resp = requests.post(url, data=kwargs['query'])
      result = resp.json()
    except (requests.ConnectionError, requests.Timeout, requests.HTTPError) as e:
      print("POST URL={0} error={1}".format(url, e))
      sys.exit(1)
    try:
      scroll_id = result['_scroll_id']
    except:
      print("result: {0}".format(result))
      sys.exit(1)
    url2 = "http://" + kwargs['host'] + ":" + "{0}".format(kwargs['port']) + "/_search/scroll?scroll=1m&search_type=scan"
    count = 0
    while True:
      try:
        resp = requests.post(url2, data=scroll_id)
        result = resp.json()
        print("{0}".format(result))
        records = len(result['hits']['hits'])
        count = count + records
        logger.info("{0} read, total records {1}...".format(records, count))
        scroll_id = result['_scroll_id']
      except (requests.ConnectionError, requests.Timeout, requests.HTTPError) as e:
        print("POST URL={0} error={1}".format(url, e))
        break
      if resp.status_code != 200:
        logger.info("status code {0}".format(resp.status_code))
        break
      if len(result['hits']['hits']) < 1:
        logger.info("Total records : {0}".format(result['hits']['total']))
        break
    return 0


def main():
    start = time.time()

    parser = make_parser()
    arguments = parser.parse_args()

    # Setup logging
    if arguments.debug:
      numeric_log_level = logging.DEBUG
      format_string = '%(asctime)s %(levelname)-9s %(name)22s %(funcName)22s:%(lineno)-4d %(message)s'
    else:
      numeric_log_level = getattr(logging, arguments.log_level.upper(), None)
      format_string = '%(asctime)s %(levelname)-9s %(message)s'
      if not isinstance(numeric_log_level, int):
        raise ValueError('Invalid log level: %s' % arguments.log_level)
    
    date_string = None
    if arguments.logformat == 'logstash':
      os.environ['TZ'] = 'UTC'
      time.tzset()
      format_string = '{"@timestamp":"%(asctime)s.%(msecs)03dZ", "loglevel":"%(levelname)s", "name":"%(name)s", "function":"%(funcName)s", "linenum":"%(lineno)d", "message":"%(message)s"}'
      date_string = '%Y-%m-%dT%H:%M:%S'

    logging.basicConfig(level=numeric_log_level,
                        format=format_string,
                        datefmt=date_string,
                        stream=open(arguments.log_file, 'a') if arguments.log_file else sys.stderr)

    logging.info("Job starting...")

    # Execute the command specified in the arguments
    argdict = arguments.__dict__
    arguments.func(**argdict)

    # dump_query(index='logstash-2014.11.12', body=kwargs['query'])
    logger.info('Done in {0} seconds.'.format(timedelta(seconds=time.time()-start)))

"""
Usage: query_dump.py query_dump --index logstash-2014.11.12 --query '{"query":{"filtered":{"query":{"bool":{"should":[{"query_string":{"query":"\"ETIMEDOUT\" AND \"kafka\" AND NOT \"LixTreatmentsEvent\""}}]}},"filter":{"bool":{"must":[{"range":{"@fields.ts":{"from":1415807603819,"to":1415811173227}}},{"fquery":{"query":{"query_string":{"query":"@fields.level:(\"ERROR\")"}}}}]}}}},"size":500}' > ~/tmp/pageviews-20141112-2.json
"""

if __name__ == '__main__':
    main()
