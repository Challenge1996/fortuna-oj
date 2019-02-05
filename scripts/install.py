import os, sys, getpass
import re, random, string

# Set default configs

oj_branch = 'new-env'

config_file = [
    '/overriding_config/local.php',
    '/overriding_config/secret.php',
    '/scripts/init-db.sql'
]

config = {
    'cookiepath': [
        'cookie_path (keep default if you know nothing)',
        '',
        '/'
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
        'apt install -y mariadb-server',
        'service mariadb restart'
    ],
    [
        'Install Redis',
        'apt install -y redis'
    ],
    [
        'Install PHP 7.2 and related components',
        'apt install -y software-properties-common apt-transport-https lsb-release ca-certificates',
        'add-apt-repository -y -u ppa:ondrej/php',
        'apt install -y php7.2-fpm php7.2-mysql php7.2-curl php7.2-gd php7.2-mbstring php7.2-xml php7.2-xmlrpc php7.2-zip php7.2-opcache php-redis',
        'service php7.2-fpm restart'
    ],
    [
        'Configure Redis to listen to unix socket',
        r'sed -i "s/.*unixsocket .*/unixsocket \/var\/run\/redis\/redis-server.sock/" /etc/redis/redis.conf',
        r'sed -i "s/.*unixsocketperm.*/unixsocketperm 770/" /etc/redis/redis.conf',
        'usermod -aG redis www-data',
        'service redis-server restart'
    ],
    [
        'Get fortuna-oj from GitHub',
        'mkdir -p /var/www/foj',
        'chown www-data:www-data /var/www/foj',
        'sudo -u www-data git clone -b ' + oj_branch + ' https://github.com/roastduck/fortuna-oj /var/www/foj'
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
    inputHandle = open('/var/www/foj' + filename)
    text = inputHandle.read()
    inputHandle.close()
    
    text = re.sub(r'{{(.*?)}}', replace, text)
    
    outputHandle = open('/var/www/foj' + filename, 'w')
    outputHandle.write(text)
    outputHandle.close()
            
# Main()

if os.getuid():
    print("You must run this script with ROOT priviledge!")
    exit()

for command_block in inst_env_command:
    execute_command_block(command_block)

output_bar('Create local settings')
print('(Input nothing to use default settings)')
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
        
# Get ready for the config files
run('cp /var/www/foj/scripts/init-db.sql.example /var/www/foj/scripts/init-db.sql')
run('cp /var/www/foj/overriding_config/local.php.example /var/www/foj/overriding_config/local.php')
run('cp /var/www/foj/overriding_config/secret.php.example /var/www/foj/overriding_config/secret.php')
        
for filename in config_file:
    replace_file(filename)
    
run('mysql < /var/www/foj/scripts/init-db.sql')
run('rm -f /var/www/foj/scripts/init-db.sql')
run('chown www-data:www-data /var/www/foj/overriding_config/local.php')
run('chown www-data:www-data /var/www/foj/overriding_config/secret.php')

execute_command_block([
    'Configure NGINX',
    'cp /var/www/foj/scripts/foj-nginx.conf /etc/nginx/sites-enabled/',
    'rm /etc/nginx/sites-enabled/default',
    'nginx -t',
    'service nginx reload'
])
