# Anakeen Admin Center

Web interfaces to admin applications and configure smart elements

Admin Center is made of different plugins.

## Register a plugin
A plugin is declared in the directory `src/config/adminPlugins` as a json file.
The structure of the json file must respect the following format :

```
{
    "my-plugin-name": {
        "title": "Plugin Title",
        "icon": "<i class='my-icon'></i>",
        "order": {
          "position": "first"
        },
        "componentPath": "/usersManagement",
        "scriptURL": "/api/v2/admin/plugin/ankAdminPlugins",
        "componentTemplate": "<my-web-component custom-prop='foo'></my-web-component>"
        "subcomponents": {
            // Other plugin definition
        }
}
```
     
The format of the json structure is validated by a json schema