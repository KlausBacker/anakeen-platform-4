# Usage

## Build local docker images

Clone `gitlab-runner-images` repo:

    $ git clone git@gitlab.anakeen.com:tools/gitlab-runner-images.git

Build image(s):

    $ cd gitlab-runner-images
    $ make -C php71pg96

## Run autotest locally

    $ ./Tests/autotest/local-run.sh php71pg96

The docker image is run with:
- `/autotest/work`: current working directory with a checkout of the source
  code.

You can also run the tests on a specific `git-ref` (e.g. for dichotomy
bisection testing):

    $ ./Tests/autotest/local-run.sh php71pg96 HEAD^^

## Setup docker DNS

The autotest requires access to hosts on the `corp.anakeen.com` domain.

Depending on your OS and configuration, this domain might not be available to
docker images.

Here are two methods to enable resolution of `corp.anakeen.com` domain for
docker images.

### Using static DNS resolver

Modify `DOCKER_OPTS` in `/etc/default/docker` to use a DNS server that can
resolve `*.corp.anakeen.com` domains (e.g. `192.168.200.1`):

    $ sudo vi /etc/default/docker
    ...
    DOCKER_OPTS="--dns 192.168.200.1"
    ...

    $ sudo vi /etc/docker/daemon.json
    {
      ...,
      "dns": [ "192.168.200.1" ]
    }

    $ sudo service docker restart

### Using dynamic dnsmasq resolver (tested on ubuntu 16.04)

Identify IP address of `docker0` network interface (e.g. `172.17.0.1`):

    $ sudo ip addr list docker0
    ...
        inet 172.17.0.1/16 scope global docker0
    ...

Configure docker's DNS with this address:

    $ sudo vi /etc/default/docker
    ...
    DOCKER_OPTS="--dns 172.17.0.1"
    ...

    $ sudo vi /etc/docker/daemon.json
    {
      ...,
      "dns": [ "172.17.0.1" ]
    }

    $ sudo service docker restart

Activate global IP routing and routing of localhost:

    $ sudo vi /etc/sysctl.conf
    ...
    net.ipv4.ip_forward = 1
    net.ipv4.conf.docker0.route_localnet = 1
    ...

    $ sysctl -p

Redirect DNS requests from docker to local dnsmasq:

    $ sudo vi /etc/rc.local
    ...
    iptables -t nat -I PREROUTING -i docker0 -s 172.17.0.0/16 -p udp --dport 53 -j DNAT --to 127.0.1.1:53
    ...

    $ sudo /etc/rc.local

