from xmlrpclib import ServerProxy, Error
import sys

data = sys.stdin.readlines()
print data.strip.split( None )

server = ServerProxy("http://bot.longlake.co.uk/xmlrpc.php")

print server

try:
    print server.bot.git.message('test', 'test')
except Error, v:
    print "ERROR", v
