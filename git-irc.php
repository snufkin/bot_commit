<?php

$server = 'http://bot.longlake.co.uk/xmlrpc.php';

post_receive();

/**
 * Compose an XML-RPC message to the server.
 */
function send($commit_id, $author, $message) {
  // method: bot_commit_recordCommit(
}

function git_config_get($name) {
  exec('git config --get', $output);
  return $output;
}

/**
 * Show commit information in a certain format.
 */
function git_show($hash, $format = 'short') {
  exec("git show --pretty=$format $hash", $output);
  return $output;
}

function git_rev_parse($hash) {
  exec("git rev-parse --short", $output);
  return $output;
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
  }
}

function get_commits($old_rev, $new_rev) {
  exec("git log --pretty=format:%H --reverse $old_rev..$new_rev", $output);
  return $output;
}

function post_receive() {
  // Information is passed via STDIN
  // It can be multiple line in case of a multi-commit push.
  $handle = fopen("php://stdin", "r");
  while (!feof($handle)) {
    $line = trim(fread($handle, 512));
    list($old_rev, $new_rev, $ref_name) = explode(' ', $line);
    $commits[$refname] = get_commits($old_rev, $new_rev);
  }
  fclose($handle);
  process_commits($commits);
}
