#!/usr/bin/env python
#
# (C) Copyright 2016-2018 Tin Le tin@le.org
# All Rights Reserved
#
from __future__ import print_function

import boto3
from datetime import datetime
import re
import click
import pprint

__version__ = '1.0.1'
hosts = {}
pp = pprint.PrettyPrinter(indent=4, depth=4)

def get_instance_name(instance, regexp):
    """
    When given instance return the instance 'Name' from name tag.
    if regexp is defined, only return Name/Value matching regexp.
    """
    instancename = ''
    try:
        p = re.compile(regexp, re.I)
        try:
            for t in instance["Tags"]:
                if t['Key'] == 'Name':
                    if regexp == '':
                        instancename += t['Value'] + ' '
                    else:
                        if p.search(t['Value']):
                            instancename += t['Value'] + ' '
                elif t['Key'] == 'DNS':
                    instancename += t['Value'] + ' '
        except KeyError as e:
            # instancename = 'Tags not defined!'
            print("\nInstanceID: {0} (IP: {1}) have no Tags!\n".format(instance["InstanceId"], instance["PrivateIpAddress"]))
        if instancename == '':
            if p.search(instance["PublicDnsName"]):
                instancename += instance["PublicDnsName"] + ' '
    except Exception as e:
        pass

    return instancename


def get_platform(instance):
    """
    When given instance ID, try to determine the Platform, if regexp
    is defined, look in tags matching regexp.
    """
    platformname = ''
    try:
        p = re.compile('(rhel|ubuntu)', re.I)
        try:
            for t in instance["Tags"]:
                if t['Key'] == 'EMR':
                    platformname = 'AWS Linux'
                elif t['Key'] == 'OS':
                    platformname = t['Value']
                elif t['Key'] == 'rhel' or t['Key'] == 'ubuntu':
                    platformname = t['Key'] + ' Linux (' + t['Value'] + ')'
                else:
                    if p.search(t['Key']):
                        platformname = t['Key'] + ': ' + t['Value']
        except KeyError as e:
            # platformname = 'Tags not defined!'
            print("\nInstanceID: {0} (IP: {1}) have no Tags!\n".format(instance["InstanceId"], instance["PrivateIpAddress"]))
        if platformname == '':
            # if instance['KeyName'] == 'windowsDB':
            if re.search('windows', instance['KeyName'], re.I):
                platformname = 'Windows'
            else:
                platformname = 'Other Linux'
    except Exception as e:
        pass

    return platformname


def dump_instance_data(instance, region='us-east-1'):
    if re.match(region, instance["Placement"]["AvailabilityZone"]):
        print("\n{0}".format('*' * 50))
        # output entire Dict object
        #print("Instance Dict Object: {0}".format(instance))

        # output value of key 'InstanceId', AZ
        print("Instance ID: {0}".format(instance["InstanceId"]))
        print("Availability Zone: {0}".format(instance["Placement"]["AvailabilityZone"]))

        # output private/public IP and DNS
        print("Private IP: {0}\nPrivate DNS: {1}\nPublic DNS: {2}".format(
            instance["PrivateIpAddress"], instance["PrivateDnsName"], instance["PublicDnsName"]))

        # output instance Tag.Key.Name
        instancename = get_instance_name(instance, '')
        print("Instance Name: {0}".format(instancename))

        # output instance Platform
        platformname = get_platform(instance)
        print("Platform : {0}".format(platformname))

        # dump content from NetworkInterfaces[]
        print("SubnetId,VpcId,NI-Id,SourceDestCheck,PublicDns,PublicIp,Primary,PrivateIp,PrivateDns")
        sn = output_subnet(instance, True)


def output_host_entry(instance, pattern):
    instancename = get_instance_name(instance, pattern)
    if instancename != '':
        # output 1 line of /etc/hosts entry
        # IP  FQDN TagKeyName
        try:
            print("{0}\t{1} {2}".format(
                instance["PrivateIpAddress"], instance["PrivateDnsName"], instancename))
        except ValueError as e:
            print("PrivateIpAddress: {0}".format(instance["PrivateIpAddress"]))
            print("PrivateDnsName: {0}".format(instance["PrivateDnsName"]))
            print("instancename: {0}".format(instancename))


def output_subnet(instance, debug):
    subnets = {}

    # NetworkInterfaces is an array
    count = 0
    for ni in instance["NetworkInterfaces"]:
        subnets[ni["SubnetId"]] = "{0}, {1}".format(ni["VpcId"], ni["PrivateIpAddress"])

        if debug:
            # print("{0}, ".format(count), end="")
            print("{0}, {1}, {2}, ".format(
                ni["SubnetId"], ni["VpcId"], ni["NetworkInterfaceId"]), end="")
            if ni["SourceDestCheck"]:
                print("true, ", end="")
            else:
                print("false, ", end="")
            try:
                print("{0}, {1}, ".format(
                    ni["Association"]["PublicDnsName"], ni["Association"]["PublicIp"]), end="")
            except:
                print(", , ", end="")
            for pia in ni["PrivateIpAddresses"]:
                if pia["Primary"]:
                    print("true, ", end="")
                else:
                    print("false, ", end="")
                print("{0}, ".format(pia["PrivateIpAddress"]), end="")
                try:
                    print("{0}".format(pia["PrivateDnsName"]))
                except:
                    print(", ")
            count += 1
    return subnets



#####################################################
@click.command()
@click.option('--pattern', '-p',
        default='',
        help='Output only hosts matching pattern. Default is ALL hosts.')
@click.option('--debug', '-d', is_flag=True,
        default=False,
        help='Debug mode.')
@click.option('--verbose', '-v', is_flag=True,
        default=False,
        help='Verbose mode.')
@click.option('--hosts/--no-hosts',
        default=True,
        help='Output /etc/hosts compatible entries (default).')
@click.option('--platform/--no-platform',
        default=False,
        help='Output instance platform (RHEL/Ubuntu/Windows/etc) as comment in /etc/hosts entries (default to no output)')
@click.option('--subnet', '-S', is_flag=True,
        default=False,
        help='Output EC2 subnet in table format')
@click.option('--region', '-r',
        default='us-east-1',
        help='AWS Region to read data from (default to us-east-1).')
def main(pattern, debug, verbose, hosts, platform, subnet, region):
    """
  Find all instances in EC2 that match `tag` and generate output that can be added
  to /etc/hosts.

  NOTE: requires python3+ and boto3 (for AWS supports)

    Tool to scan EC2 resources and generate list of hosts in a format compatible with
    /etc/hosts.  This is for generating/updating /etc/hosts file so we are not dependent
    on DNS.

    It work via Tags Name/Value pair and PublicDnsName resource attribute.  It will use
    both if found and generate something similar to this:

    1.2.3.4 Tags.Value PublicDnsName
    """
    ec2 = boto3.client('ec2', region_name=region)
    try:
        response = ec2.describe_instances()
        if subnet:
            print("SubnetId,VpcId,NI-Id,SourceDestCheck,PublicDns,PublicIp,Primary,PrivateIp,PrivateDns")
        if hosts:
            print("\n##############################################\n# Generated by name2hosts\n# On {0}\n".format(datetime.today()))
        for reservation in response["Reservations"]:
            for instance in reservation["Instances"]:
                if instance["State"]["Name"] == 'running':
                    if debug:
                        dump_instance_data(instance, region)
                    if hosts:
                        if platform:
                            print("# {0}".format(get_platform(instance)))
                        output_host_entry(instance, pattern)
                    if subnet:
                        sn = output_subnet(instance, debug)
                        for k in sn:
                            print("{0}, {1}".format(k, sn[k]))
    ##############################################
    # FIXME - Should really be NoCredentialsError
    except NoCredentialsError as e:
        print("\nGot exception {e}\n".format(e=e))
        print("Credentials Error!  Verify that you have setup ~/.aws/credentials and ~/.aws/config files")
        print("See https://boto3.readthedocs.io/en/latest/guide/quickstart.html for more details.")
    except Exception as e:
        print("\nGot exception {e}\n".format(e=e))

def modify_usage_error(main_command):
    '''
    Function to append the help menu to a usage error

    :param main_command: top-level group or command object constructed by click wrapper
    :return: None
    '''
    def show(self, file=None):
        if file is None:
            file = click._compat.get_text_stderr()
        color = None
        if self.ctx is not None:
            color = self.ctx.color
            click.utils.echo(self.ctx.get_usage() + '\n', file=file, color=color)
        click.utils.echo('Error: %s\n\nThis is name2hosts v{0}\n\nFor more help, run \'name2hosts --help\'\n'.format(__version__) % self.format_message(), file=file, color=color)

    click.exceptions.UsageError.show = show

if __name__ == '__main__':
    modify_usage_error(main)
    main()
