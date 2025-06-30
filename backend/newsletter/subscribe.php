$email=$_POST['email']??'';
file_put_contents(__DIR__.'/emails.txt',$email.PHP_EOL,FILE_APPEND);
echo 'subscribed';
