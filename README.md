HSMM-Pi with Wireless AP
========================

HSMM-Pi is a set of tools designed to easily configure the Raspberry Pi to function as a High-Speed Multimedia (HSMM) or Broadband-Hamnet (BBHN) wireless node.  HSMM and BBHN offer radio amateurs (HAMs) the ability to operate high-speed data networks in the frequencies shared with unlicenced users of 802.11 b/g/n networking equipment.  HAMs can operate HSMM or BBHN at higher power with larger antennas than are available to unlicensed users.  The HSMM-Pi project makes it possible to run an HSMM or BBHN mesh node on the Raspberry Pi.  The project has been tested to work on other embedded computing platforms, including the BeagleBone and BeagleBone Black.

The HSMM-Pi project can used by people not possessing an amateur radio license so long as they are in compliance with the transmission rules set by the FCC or the local regulating body.  This typically means sticking with the WiFi antenna provided with your WiFi adapter.

HSMM-Pi Blog:
http://hsmmpi.wordpress.com/

For a video tour, see the following YouTube video:
http://www.youtube.com/watch?v=ltUAw02vfqk

The project consists of a PHP web application that is used to configure and monitor the mesh node, and an installation shell script that installs dependencies and puts things in the right spots.

The HSMM-Pi project is designed to run on Ubuntu 12.04 systems.  Rather than providing an OS image for HSMM-Pi, I've instead created an installation script that will transform a newly-imaged host into an HSMM-Pi node.  This has several benefits:

 * Greater transparency:  You can see exactly which changes are made to the base system by looking at the [install shell script](https://github.com/urlgrey/hsmm-pi/blob/master/install.sh).
 * Easier to port to more platforms: Any platform that runs the supported Ubuntu releases ought to be capable of running HSMM-Pi
 * Easier to host:  I only need to post the installation script and webapp files on Github and it's done.
 * Easier to seek support: Ubuntu is widely used and supported, no need to introduce another customization.

This fork allows the implementation with two wireless interfaces, one for the Mesh Network and another as Access Point for a wireless LAN.

Hardware Requirements
=====================
HSMM-Pi has been tested to work with the Raspberry Pi running the Raspbian OS.  The requirements for each are listed below.

Raspberry-Pi Node:

 *  Raspberry Pi (256MB or 512MB) or Raspberry Pi 2
 *  2 USB WiFi adapter (tested with the N150 adapter using the Ralink 5370 chipset, and with the Alfa AWSU036NH)
 *  SD memory card (4GB minimum)

Modes
=====
The HSMM-Pi can function as an internal mesh node or as a gateway mesh node.  A description of each is provided below.

Mesh Gateway Node
=================
The Mesh Gateway is capable of routing traffic throughout the mesh, and provides an Internet link to the mesh through the any interface port.  The gateway obtains a DHCP lease on the interface, and advertises its Internet link to mesh nodes using OLSR.


Internal Mesh Node/AP
==================
This node is capable of routing traffic throughout the mesh and providing mesh access to any hosts connected to this own access point.  The node can run a DHCP server that issues DHCP leases to any hosts on the connection.  It also runs a DNS server that can provide name resolution for mesh nodes and Internet hosts.  The following sequence shows how the two types of nodes can be deployed:

```
(Client1) --> (Internal Mesh Node/AP) --> 
(Ad-Hoc WiFi Network) --> (Mesh Gateway) --> (Internet)
```

There could be any number of mesh nodes in the Ad-Hoc WiFi Network.  The route among the nodes is managed entirely with OLSR.

I've done all of my testing with N150 USB wifi adapters that use the Ralink 5370 wireless chipset.  These adapters are cheap (~$7 USD), compact, and easy to come by.  They also use drivers that are bundled with most Ubuntu distributions, making setup easy.  The N150 adapter tested included a threaded antenna connector that should make it easy to add a linear amplifier and aftermarket antenna (outside the scope of the HSMM-Pi project).

Raspberry Pi Installation
=========================

1.  Download the Raspbian Jessie Lite disk image on your Mac/PC/whatever (https://downloads.raspberrypi.org/raspbian_lite_latest)
1.  Write the image to an SD memory card.  This involves formatting the SD card; I recommend the steps described at http://elinux.org/RPi_Easy_SD_Card_Setup
1.  Insert the card into a Raspberry Pi
1.  Connect the wired Ethernet port on the Pi to a network with Internet access
1.  Apply power to the Pi
1.  Login to the Pi, either through an SSH session or the console, using the 'pi' account
1.  Run the Raspberry Pi Setup program:

        sudo raspi-config
1.  Expand the filesystem to fill the SD memory card
1.  Change the password for the 'pi' account
1. If installing over an SSH connection to the Pi, then I recommend you install 'screen' (sudo apt-get install screen) to ensure that the installation script is not stopped prematurely if you lose connectivity with the Pi.  This is optional, but I highly recommend using screen if installing over the network.  You can find more info on screen here: http://linux.die.net/man/1/screen
1.  Run the following commands to download the HSMM-Pi project and install

        sudo apt-get install -y git
        git clone https://github.com/urlgrey/hsmm-pi.git
        cd hsmm-pi
        sh install.sh

1.  Login to the web application on the Pi:
http://(wired Ethernet IP of the node):8080/
1.  Access the Admin account using the 'admin' username and 'changeme' password.
1.  Change the password for HSMM-Pi
1.  Configure as either an Internal or Gateway node


Upgrade Steps
=============
This is experimental, and you should fall back to a fresh installation if things aren't functioning as you'd expect.  This is supported only on the HEAD of the master branch at this time.

1. Login to the host using SSH or the console
1. Run the following commands to upgrade:

        cd ~/hsmm-pi
        git pull
        sh install.sh
1. Access the web UI and check the configuration.  Save the Network and Location settings, even if no changes are needed.
1. If the save operation fails, then you might need to replace the SQLite database file due to database schema changes.  Run the following command:

        cd ~/hsmm-pi/
        sudo cp src/var/data/hsmm-pi/hsmm-pi.sqlite /var/data/hsmm-pi/
1. Repeat the step of reviewing and saving the configuration through the web UI.

Internal Mesh Node Configuration
================================
This represents the minimum set of steps:

1. Select Admin->Network from the menubar
1. Configure the WiFi interface:
    1.  Specify an IP address that will be unique throughout the mesh network.  This will be different every mesh node.  A default will be provided based on the last 3 fragments of the WiFi adapter MAC address.
1. Configure the Wired interface:
    1. Set the Wired interface mode to LAN or write the wireless interface for AP
1. Configure the Mesh settings
    1. Specify your amatuer radio callsign (i.e. KK6DCI)
    1. Specify your node name, likely a composition of your callsign and a unique number in your mesh (i.e. KK6DCI-7)
1. Click 'Save'
1. On terminal, configure as AP

        sudo apt-get install hostapd
1. Edit ```/etc/default/hostapd``` and replace:

        DAEMON_CONF="/etc/hostapd/hostapd.conf"
1. And create ```/etc/hostapd/hostapd.conf``` with the following parameters:

        interface=wlan1
        driver=nl80211
        ssid=<YOUR NEW SSID>
        channel=1
        hw_mode=g
        macaddr_acl=0
        ignore_broadcast_ssid=0
        beacon_int=100 # This sets how often the WiFi will send a beacon out.
        auth_algs=3
        wmm_enabled=1
        wpa=3
        wpa_passphrase=<YOUR-PASSPHRASE>
        wpa_key_mgmt=WPA-PSK
        wpa_pairwise=TKIP
        rsn_pairwise=CCMP

1. If successful, click the 'Reboot' button in the alert and proceed.

 * Troubleshooting: [rt2x00usb_vendor_request: Error](https://www.raspberrypi.org/forums/viewtopic.php?t=22623)


Gateway Node Configuration
================================
This represents the minimum set of steps:

1. Select Admin->Network from the menubar
1. Configure the WiFi interface:
    1. Specify an IP address that will be unique throughout the mesh network.  This will be different every mesh node.  A default will be provided based on the last 3 fragments of the WiFi adapter MAC address.
1. Configure the Wired interface:
    1. Set the Wired interface mode to WAN
1. Configure the Mesh settings
    1. Specify your amatuer radio callsign (i.e. KK6DCI)
    1. Specify your node name, likely a composition of your callsign and a unique number in your mesh (i.e. KK6DCI-7)
1. Click 'Save'
1. If successful, click the 'Reboot' button in the alert and proceed.

FAQ
====

A list of frequently asked questions can be found on [this wiki](https://github.com/cristhoper/hsmm-pi/wiki), at the [FAQ page](https://github.com/cristhoper/hsmm-pi/wiki/FAQ).
