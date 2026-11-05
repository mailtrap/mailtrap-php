UPGRADE FROM 1.x to 2.0
=======================

### Email Layers
* Added a short method to get one of the Email layers (Sending/Bulk/Sandbox) depending on config params `MailtrapClient::initSendingEmails()`
  *  string $apiKey
  *  bool $isBulk = false
  *  bool $isSandbox = false
  *  int $inboxId = null
* **BC BREAK**: In Sandbox layer `inboxId` should be passed at the client level.

  __Before__: 
  ```php
  $mailtrap = new MailtrapClient(new Config(getenv('MAILTRAP_API_KEY')));
  
  $response = $mailtrap
    ->sandbox()
    ->emails()
    ->send($email, 1000001); # <--- inboxId here
  ```
  __After__:
  ```php
  # short method using `initSendingEmails`
  $mailtrap = MailtrapClient::initSendingEmails(
    apiKey: getenv('MAILTRAP_API_KEY'), #your API token from here https://mailtrap.io/api-tokens
    isSandbox: true, # required param for sandbox sending
    inboxId: getenv('MAILTRAP_INBOX_ID') # <--- inboxId here
  );
  
  # or using the client directly (old variant)
  $mailtrap = (new MailtrapClient(new Config(getenv('MAILTRAP_API_KEY'))))
    ->sandbox()
    ->emails(getenv('MAILTRAP_INBOX_ID')); # <--- inboxId here
 
  $response = $mailtrap->send($email);
  ```

### General API
* **BC BREAK**: Rebuild `Accounts` & `Permissions` & `Users` layers ([examples](examples/general))

  __Before__: 
  ```php
  $mailtrap = new MailtrapClient(new Config(getenv('MAILTRAP_API_KEY'))); # no changes here
  
  $response = $mailtrap
    ->general()
    ->permissions()
    ->getResources(getenv('MAILTRAP_ACCOUNT_ID')); # <--- accountId here
  
  $response = $mailtrap
    ->general()
    ->users()
    ->getList(getenv('MAILTRAP_ACCOUNT_ID'));  # <--- accountId here
  ```
  __After__:
  ```php
  // all permissions endpoints
  $response = $mailtrap
    ->general()
    ->permissions(getenv('MAILTRAP_ACCOUNT_ID')) # <--- accountId here
    ->getResources();
  
  // all users endpoints
  $response = $mailtrap
    ->general()
    ->users(getenv('MAILTRAP_ACCOUNT_ID')) # <--- accountId here
    ->getList();
  ```

### Sandbox API
* **BC BREAK**: Rebuild `Projects` & `Messages` & `Attachments` & `Inboxes` layers ([examples](examples/sandbox))

  __Before__:
  ```php
  $mailtrap = new MailtrapClient(new Config(getenv('MAILTRAP_API_KEY'))); # no changes here
  
  $response = $mailtrap
    ->sandbox()
    ->inboxes()
    ->getList(getenv('MAILTRAP_ACCOUNT_ID')); # <--- accountId here
  ```
  __After__:
  ```php
  // all sandbox(testing) endpoints: projects, messages, attachments, inboxes
  $response = $mailtrap
    ->sandbox()
    ->inboxes(getenv('MAILTRAP_ACCOUNT_ID')) # <--- accountId here
    ->getList();
  ```

### Email Template class
* Added `MailtrapEmail` wrapper (MIME) for easy use category, custom variables, template uuid, etc.

  __Before__:
  ```php
  $email = (new Email())
      ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // <--- you should use your domain here that you installed in the mailtrap.io admin area (otherwise you will get 401)
      ->replyTo(new Address('reply@YOUR-DOMAIN-HERE.com'))
      ->to(new Address('example@gmail.com', 'Jon'))
  ;

  // Template UUID and Variables
  $email->getHeaders()
      ->add(new TemplateUuidHeader('bfa432fd-0000-0000-0000-8493da283a69'))
      ->add(new TemplateVariableHeader('user_name', 'Jon Bush'))
      ->add(new TemplateVariableHeader('next_step_link', 'https://mailtrap.io/'))
      ->add(new TemplateVariableHeader('get_started_link', 'https://mailtrap.io/'))
      ->add(new TemplateVariableHeader('onboarding_video_link', 'some_video_link'))
  ;
  ```
  
  __After__:
  ```php
  use Mailtrap\Mime\MailtrapEmail;
  
  $email = (new MailtrapEmail()) # <--- new MIME class with template support
      ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // <--- you should use your domain here that you installed in the mailtrap.io admin area (otherwise you will get 401)
      ->replyTo(new Address('reply@YOUR-DOMAIN-HERE.com'))
      ->to(new Address('example@gmail.com', 'Jon'))
      ->templateUuid('bfa432fd-0000-0000-0000-8493da283a69') 
      ->templateVariables([
          'user_name' => 'Jon Bush',
          'next_step_link' => 'https://mailtrap.io/',
          'get_started_link' => 'https://mailtrap.io/',
          'onboarding_video_link' => 'some_video_link'
      ])
  ;
  ```
