# vagrant install script
# adapted from https://gist.github.com/danielpataki/0861bf91430bf2be73da
sudo apt-get update

sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'

sudo apt-get install -y vim curl python-software-properties
sudo add-apt-repository -y ppa:ondrej/php5-oldstable
sudo apt-get update

sudo apt-get install -y php5 apache2 libapache2-mod-php5 php5-curl php5-gd php5-mcrypt php5-readline mysql-server-5.5 php5-mysql git-core php5-xdebug

sudo a2enmod rewrite

sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/apache2/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/apache2/php.ini
sed -i "s/disable_functions = .*/disable_functions = /" /etc/php5/cli/php.ini

sudo service apache2 restart

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# ALC specific setup

# create available site for /var/www/alc.org
sudo cat > /etc/apache2/sites-available/alc.conf <<'apache'
<VirtualHost *:80>
ServerName alc.dev
ServerAlias *.alc.dev
DocumentRoot /var/www/alc-dev

<Directory /var/www/alc-dev>
  Options -Indexes +FollowSymLinks
  DirectoryIndex index.php
  AllowOverride All
  Order allow,deny
  Allow from all

  Require all granted
  Satisfy Any
</Directory>
</VirtualHost>
apache

# add site and restart apache
sudo a2ensite alc.conf
sudo service apache2 restart

# set up MySQL database
mysql -uroot -proot -e "create database alc_wordpress"
