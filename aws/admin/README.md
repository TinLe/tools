#
# (C) Copyright 2016-2018 Tin Le tin@le.org
# All Rights Reserved
#
# admin tools
Administration tools for AWS

    Scripts and other assets techops/iops uses to admin AWS.   This is separate from infrastructure
    repo (that is more structured and geared toward clouds).
    
    This repo is for ad-hoc stuffs that does not necessarily require the full blown cloud
    deploy/updates.

## name2host.py

    Tool to scan EC2 tags and generate list of hosts in a format compatible with /etc/hosts.
    This is for generating/updating /etc/hosts file so we are not dependent on DNS.

    It work via Tags Name/Value pair and also uses PublicDnsName resource attribute.  It will use
    both if found and generate something similar to this:

    1.2.3.4 Tag.Value PublicDnsName

    REQUIRED

    Assumed that your AWS credentials are in ~/.aws/credentials and that you have the required
    permission to read EC2 resources.  The permissions only have to be read.

    We are using the pex util to package up the tool.  pex = Python Executable.
    What pex does is to package up the entire virtualenv.

## list-amis-in-use.sh

    List the AMIs that we are currently using, e.g. have an actual instance using the AMI (note
    that the instance does not have to be running to be counted).
