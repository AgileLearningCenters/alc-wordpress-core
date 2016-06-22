Agile Learning Centers Network Site
====

Welcome to the ALC Network website code. It's a Wordpress Buddypress site.

# Setting up local Dev server

We use vagrant to create virtual servers to run this code locally. If you use vagrant most of the work is done for you.

## Install vagrant

Check out [Vagrant's install page](https://docs.vagrantup.com/v2/installation/index.html) to get it on your system.

## Clone the git repository

Open up a terminal shell and go to the directory where you'd like the ALC code to live, then clone it from git.

`git clone git@github.com:AgileLearningCenters/alc-wordpress-core.git`

This will place the core files into your computer!

## Prevision the Vagrant server

All the settings for the virtual machine (VM) are in the `Vagrantfile` you simply have to run `vagrant up` in the terminal from the `alc-wordpress-core` folder. So let's do that:

```
cd ~/alc-wordpress-core
vagrant up
```

The first time you do this it will take a while because the image of Ubuntu must be downloaded. Next it will fire up the virtual machine, finally it will "provision" it by running `install.sh`. This set's up apache and other things on the VM

## Add entries to your host file

We configure everything up to run locally on `http://alc.dev` but you'll need to update your hostfile. The server's IP address is set to be `192.168.99.99` so you'll need to add the following line(s) to your host file. [List of host file locations here](https://en.wikipedia.org/wiki/Hosts_(file)#Location_in_the_file_system)

```
# alc vagrant server
192.168.99.99 alc.dev
192.168.99.99 www.alc.dev
192.168.99.99 starterkit.alc.dev
192.168.99.99 alf.alc.dev
```

You'll have to add subdomain sites manually! Bummer, let me know if we can fix this.

## Download database dump

You need a dump of the live site's database, for now you'll need to ask drew@alc.network for that.

## Setup the VM database

Move the database dump into the Vagrant sync folder, which happens to be the `alc-wordpress-core` folder. It will now show up in your VM at `/var/www/alc-dev/`

A database is already set up in the VM called `alc_wordpress` where we will upload the database dumb. So SSH into your vagrant VM using:

`vagrant ssh`

Pop over to the synced folder and WP web root:

`cd /var/www/alc-dev/`

If you got a tar.gz, first untar it:

`tar -xvf name-of-db-dump.sql.tar.gz`

Then run:

`mysql -uroot -proot alc_wordpress < name-of-db-dump.sql`

*Replace name-of-db-dump.sql with the actual name of the file*

## Create and configure the wp-config.php file

You can copy `wp-config-template.php` to `wp-config.php`

Set the database name to `alc_wordpress` and mysql user and pass are `root`

That should do it!
