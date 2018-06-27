# prestashop-pod-sso
For displaying pod login button add `{hook h="displayPodLogin"}` hook where you want, in the .tpl file in theme

## Installation
for installing copy the directory to prestashop modules directory and rename it to podss

## Styling
You can use `pod-sso-a` class for styling hyperlink and `pod-sso-img` for styling pod logo

## Configuration
After installation a configuration page will be opened, if not you can open configuration page from modules page in admin area, then you must fill the form with appropriate values. Client Id, Client Secret, API token and Guild Codecan be copied from your [business panel](http://services.pod.land/) and Oauth server, Pay url are filled with production server by default. if you want to change to sandbox server you can use sandbox links for [pod documentation](http://docs.pod.land/v1.0.8.0/Developer/Introduction/327/Urls)
