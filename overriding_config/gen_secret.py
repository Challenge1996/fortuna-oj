#!/usr/bin/env python2

# This script copy `secret.php.example` to `secret.php` and replace some
# strings. For any {{foobar}} like string, this script will ask the user
# to input the value of "foobar". Specially, {{random}} will replaced by
# a random string, and {{*password*}}, where "*" is wildcard, will be
# asked as they are password.

import re, random, string, getpass, sys

inputHandle = open('secret.php.example')
text = inputHandle.read()
inputHandle.close()

def replace(match):
	s = match.group(1)
	if s == 'random':
		return ''.join(random.choice(string.letters) for i in range(20))
	if s.find('password') != -1:
		while True:
			pwd = getpass.getpass('Input %s: '%(s))
			confirm = getpass.getpass('Confirm %s: '%(s))
			if pwd == confirm:
				return pwd
	print 'Input %s: '%(s),
	return sys.stdin.readline().rstrip('\n')

text = re.sub(r'{{(.*?)}}', replace, text)

outputHandle = open('secret.php', 'w')
outputHandle.write(text)
outputHandle.close()

