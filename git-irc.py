from xmlrpclib import ServerProxy, Error
import sys
import re
import subprocess
import traceback

POST_RECEIVE_LOGFILE = 'hooks.post-receive-logfile'
XMLRPC_SERVER = 'http://d/bot/xmlrpc.php'

"""Object to handle the communication to the XML-RPC server"""
class Messenger(object):
  def __init__(self, server):
    self.server = server

  def send(self, repo, commit_id, author, message):
    self.server.bot_commit.recordCommit(repo, commit_id, author, message)

def git_config_get(name):
  p = subprocess.Popen(['git', 'config', '--get', name], 
                       stdout=subprocess.PIPE)
  # Cut off the last \n character.
  return p.stdout.read()[:-1]

def git_show(hash, format = 'short'):
  p = subprocess.Popen(['git', 'show', '--pretty=' + format, hash], stdout=subprocess.PIPE)
  return p.stdout.read()

def git_remote():
  p = subprocess.Popen(['git', 'remote', 'show', '-n', 'origin'], stdout=subprocess.PIPE)
  return p.stdout.read()

def git_rev_parse(hash, short=False):
  args = ['git', 'rev-parse']
  if short:
    args.append('--short')
  args.append(hash)
  p = subprocess.Popen(args, stdout=subprocess.PIPE)
  # Cut off the last \n character.
  return p.stdout.read()[:-1]
    
def process_commits(commits, messenger):
  for ref_name in commits.keys():
    use_index = len(commits[ref_name]) > 1
    for i, commit in enumerate(commits[ref_name]):
      commit_hash = git_rev_parse(commit, short=True)
      if use_index:
        commit_id = '%s commit (#%d) %s' % (ref_name, i + 1, commit_hash)
      else:
        commit_id = '%s commit %s' % (ref_name, commit_hash)
      raw_message = git_show(commit, 'format:%cn%n%s').split('\n')
      raw_repo = git_remote().split('\n')[1]
      match = re.search(r':\s(.+)$', raw_repo)
      # TODO match only the last part, but that is only there at remote repositories.
      assert match
      repo = match.group(1)
      author = raw_message[0]
      message = raw_message[1]
      messenger.send(repo, commit_id, author, message)
            

def get_commits(old_rev, new_rev):
  p = subprocess.Popen(['git', 'log', '--pretty=format:%H', '--reverse',  
                        '%s..%s' % (old_rev, new_rev)], 
                       stdout=subprocess.PIPE)
  return p.stdout.read().split('\n')

def parse_post_receive_line(l):
  return l.split()

def post_receive(messenger):
  lines = sys.stdin.readlines()
  commits = {}
  for line in lines:
      old_rev, new_rev, ref_name = parse_post_receive_line(line)
      commits[ref_name] = get_commits(old_rev, new_rev)
  process_commits(commits, messenger)

def main():
  #log_file_path = git_config_get(POST_RECEIVE_LOGFILE)
  server = ServerProxy(XMLRPC_SERVER)
  messenger = Messenger(server)
  post_receive(messenger)

if __name__ == '__main__':
  main()
