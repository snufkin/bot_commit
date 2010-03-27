<?php

include 'xmlrpc.inc';

$server = 'http://bot.longlake.co.uk/xmlrpc.php';

post_receive();

/**
 * Compose an XML-RPC message to the server.
 */
function send($commit_id, $author, $message) {
  $method = 'bot_commit.recordCommit';
  $arguments = array($commit_id, $author, $message);

  $parameters = array(
    $server,
    $method,
  );

  if (is_array($arguments)) {
    $parameters = array_merge($parameters, $arguments);
  }
  print_r($parameters);

  // The parameters cant be passed as an array to xmlrpc(). 
  //$result = _xmlrpc($parameters);
}

/**
 * Show commit information in a certain format.
 */
function git_show($hash, $format = 'short') {
  exec("git show --pretty=$format $hash", $output);
  return $output;
}

function git_rev_parse($hash) {
  exec("git rev-parse --short $hash", $output);
  return $output[0];
}

function process_commits($commits) {
  $delta = 0;
  foreach ($commits as $refname => $commit) {
    $delta++;
    $use_index = (count($commit) > 1);
    $commit_hash = git_rev_parse($commit);
    $commit_id = $use_index ? "$refname commit $commit_hash" : 
      "$refname commit (#$delta) $commit_hash";
    $raw_message = git_show($commit_hash, 'format:%cn%n%s');
    $author = $raw_message[0];
    $message = $raw_message[1];
    send($commit_id, $author, $message);
  }
}

function get_commits($old_rev, $new_rev) {
  exec("git log --pretty=format:%H --reverse $old_rev..$new_rev", $output);
  return $output[0];
}

function post_receive() {
  // Information is passed via STDIN
  // It can be multiple line in case of a multi-commit push.
  $handle = fopen("php://stdin", "r");
  while (!feof($handle)) {
    $line = trim(fread($handle, 512));
    if ($line) {
      list($old_rev, $new_rev, $refname) = explode(' ', $line);
      $commits[$refname] = get_commits($old_rev, $new_rev);
    }
  }
  fclose($handle);
  process_commits($commits);
}

