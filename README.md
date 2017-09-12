opauth-pinterest
================

[Opauth](https://github.com/opauth/opauth) strategy for pinterest.com authentication.

Based on Opauth's Instagram Oauth2 Strategy.

Getting started
----------------
1. Install opauth-pinterest:
   ```bash
   cd path_to_opauth/Strategy
   git clone git@github.com:tykarol/opauth-pinterest.git Pinterest
   ```

2. Create a Pinterest application athttps://developers.pinterest.com/apps/
   - Make sure that redirect URI is set to actual OAuth 2.0 callback URL, usually `http://path_to_opauth/pinterest/oauth2callback`

3. Configure opauth-pinterest strategy.

4. Direct user to `http://path_to_opauth/pinterest` to authenticate


Strategy configuration
----------------------

Required parameters:

```php
<?php
'Pinterest' => array(
  'client_id' => 'YOUR CLIENT ID',
  'client_secret' => 'YOUR CLIENT SECRET'
)
```

Optional parameters:
`scope`, `response_type`
For `scope`, separate each scopes with a comma (,). Eg. `read_public,write_public`. All available scops can be found [here](https://developers.pinterest.com/docs/api/overview/#permission-scopes).


References
----------
- [Developer Documentation](https://developers.pinterest.com/docs/)

License
---------
opauth-pinterest is MIT Licensed
