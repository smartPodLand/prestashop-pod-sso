# prestashop-pod-sso
For displaying pod login button add `{hook h="displayPodLogin"}` hook where you want, in the .tpl file in theme.

## Installation
If you download the module from github, first unzip it then rename `prestashop-pod-sso-master` to `podsso` and compress it as a zip format. Then use prestashop module installer for uploading the module to your server. 
## Styling
You can use `pod-sso-a` class for styling hyperlink and `pod-sso-img` for styling pod logo
or if you want to change it completely edit hookDisplayPodLogin method in podsso.php file

## Configuration
After installation, a configuration page will be opened, if not, you can open configuration page from modules page in admin area, then you must fill the form with appropriate values. Client Id, Client Secret, API token and Guild Code, can be copied from your [business panel](http://services.pod.land/) and Oauth server, Pay url are filled with production server by default. if you want to change to sandbox server you can use sandbox links from [pod documentation](http://docs.pod.land/v1.0.8.0/Developer/Introduction/327/Urls)

## Troubleshooting
If pod button does not appear where you put it in your template try clearing cache from Advanced Paramaeters -> Performance in admin menu and also check its position from Modules and services -> positions to be `displayLogin`
if you face error page with 500 code for seeing more detail go to config/defines.inc.php and set '_PS_MODE_DEV' and contact module author. 
## Redirect URI
For setting redirect uri in pod bussiness panel user this structure: `[your-domain]/[language-code]/module/podsso/handler`
