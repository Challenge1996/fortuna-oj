import os, sys, getpass
import re, random, string

# Set default configs

oj_branch = 'new-env'
oj_name = '' # Input later

config_file = [
    '/overriding_config/local.php',
    '/overriding_config/secret.php',
    '/scripts/init-db.sql',
    '/application/daemon.php',
    '/scripts/foj-nginx.conf',
    '/scripts/yaujpushd',
    '/scripts/daemon.json'
]

config = {
    'oj_name': [
        'name of the fortuna-oj directory',
        '',
        'foj'
    ],
    'db_user': [
        'username of the new database user',
        '',
        'foj'
    ],
    'db_pwd': [
        'password of the new database user',
        '',
        'foj'
    ],
    'db_name': [
        'name of the new database',
        '',
        'foj'
    ]
}

inst_env_command = [
    [
        'Install Git',
        'apt install -y git'
    ],
    [
        'Install NGINX',
        'apt install -y nginx'
    ],
    [
        'Install MariaDB',
        'apt install -y mariadb-server'
    ],
    [
        'Install Redis',
        'apt install -y redis'
    ],
    [
        'Install PHP 7.2 and related components',
        'apt install -y software-properties-common apt-transport-https lsb-release ca-certificates',
        'add-apt-repository -y -u ppa:ondrej/php',
        'apt install -y php7.2-fpm php7.2-mysql php7.2-curl php7.2-gd php7.2-mbstring php7.2-xml php7.2-xmlrpc php7.2-zip php7.2-opcache php-redis'
    ],
    [
        'Configure Redis to listen to unix socket',
        r'sed -i "s/.*unixsocket .*/unixsocket \/var\/run\/redis\/redis-server.sock/" /etc/redis/redis.conf',
        r'sed -i "s/.*unixsocketperm.*/unixsocketperm 770/" /etc/redis/redis.conf',
        'usermod -aG redis www-data',
        'service redis-server restart'
    ],
    [
        'Configure max_connections for MariaDB',
        r'grep "max_connections" /etc/mysql/mariadb.conf.d/50-server.cnf; if [ $? -eq 0 ]; then sed -i "s/.*max_connections.*/max_connections         = 32768/" /etc/mysql/mariadb.conf.d/50-server.cnf; else echo "echo \"\n[mysqld]\nmax_connections = 32768\" >> /etc/mysql/my.cnf" | sudo sh; fi',
        'service mariadb restart'
    ],
    [
        'Configure env[PATH] for PHP',
        r'sed -i "s/.*env\[PATH\].*/env\[PATH\] = \/usr\/local\/bin:\/usr\/bin:\/bin/" /etc/php/7.2/fpm/pool.d/www.conf',
        'service php7.2-fpm restart'
    ],
    [
        'Install YAUJ from GitHub',
        'mkdir -p /home/judge/src',
        'git clone --depth=1 https://github.com/roastduck/YAUJ /home/judge/src/yauj',
        '/home/judge/src/yauj/init-env_bionic.sh',
        'cd /home/judge/src/yauj && make',
        'cd /home/judge/src/yauj && make install',
    ],
    [
        'Install vfk\'s sandbox',
        'git clone --depth=1 https://github.com/roastduck/vfk_uoj_sandbox /home/judge/src/vfk_uoj_sandbox',
        'cd /home/judge/src/vfk_uoj_sandbox && make',
        'cd /home/judge/src/vfk_uoj_sandbox && make install'
    ]
]

def run(command):
    ret = os.system(command) << 8
    if ret:
        exit()

def output_bar(info):
    print()
    print('*' * len(info))
    print(info)
    print('*' * len(info))
    
def execute_command_block(command_block):
    output_bar(command_block[0])
    for command in command_block[1:]:
        run(command)
        
def replace(match):
    s = match.group(1)
    if s == 'random':
        return ''.join(random.choice(string.ascii_letters + string.digits) for i in range(20))
    elif s in config:
        return config[s][1]
    else:
        print('Unknown variable: ' + s)
        exit()
        
def replace_file(filename):
    inputHandle = open('/var/www/%s%s.example' % (oj_name, filename))
    text = inputHandle.read()
    inputHandle.close()
    
    text = re.sub(r'{{(.*?)}}', replace, text)
    
    outputHandle = open('/var/www/%s%s' % (oj_name, filename), 'w')
    outputHandle.write(text)
    outputHandle.close()
            
# Main()

if os.getuid():
    print("You must run this script with ROOT priviledge!")
    exit()

output_bar('Collect local settings')
print('(Empty input will use default settings)')
for key, values in config.items():
    if key.find('pwd') != -1:
        while True:
            pwd = getpass.getpass('Input %s (default: %s): ' % (values[0], values[2]))
            confirm = getpass.getpass('Confirm %s: ' % (values[0]))
            if pwd == confirm:
                if pwd == '':
                    pwd = values[2]
                config[key][1] = pwd
                break
            else:
                print('Passwords mismatch!')
    else:
        info = input('Input %s (default: %s): ' % (values[0], values[2]))
        if info == '':
            info = values[2]
        config[key][1] = info

oj_name = config['oj_name'][1]

for command_block in inst_env_command:
    execute_command_block(command_block)

execute_command_block([
    'Get fortuna-oj from GitHub',
    'mkdir -p /var/www/' + oj_name,
    'chown www-data:www-data /var/www/' + oj_name,
    'sudo -u www-data git clone --depth=1 -b %s https://github.com/roastduck/fortuna-oj /var/www/%s' % (oj_branch, oj_name)
])

output_bar("Create local settings")
for filename in config_file:
    replace_file(filename)
    run('chown www-data:www-data /var/www/%s%s' % (oj_name, filename))
    
run('mysql < /var/www/%s/scripts/init-db.sql' % (oj_name))
run('rm -f /var/www/%s/scripts/init-db.sql' % (oj_name))

execute_command_block([
    'Configure NGINX',
    'mv /var/www/%s/scripts/foj-nginx.conf /etc/nginx/sites-enabled/' % (oj_name),
    'chown root:root /etc/nginx/sites-enabled/foj-nginx.conf',
    'rm /etc/nginx/sites-enabled/default',
    'nginx -t',
    'service nginx reload'
])

execute_command_block([
    'Setup services and daemons',
    'echo "*/1 * * * * php /var/www/%s/application/daemon.php" | crontab -u www-data -' % (oj_name),
    'mkdir -p /etc/yauj',
    'mv /var/www/%s/scripts/daemon.json /etc/yauj/' % (oj_name),
    'chown root:root /etc/yauj/daemon.json',
    'cp /var/www/%s/scripts/yaujd /etc/init.d/' % (oj_name),
    'mv /var/www/%s/scripts/yaujpushd /etc/init.d/' % (oj_name),
    'chown root:root /etc/init.d/yauj*',
    'chmod 755 /etc/init.d/yauj*',
    'update-rc.d yaujd defaults',
    'update-rc.d yaujpushd defaults'
])

output_bar('!! Success !!')
print("You are REQUIRED to reboot your system.")
print("Have fun with fortuna-oj!")
print()
