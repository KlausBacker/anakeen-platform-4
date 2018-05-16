# Anakeen Admin Center

Web interfaces to admin applications and configure smart elements

Admin Center is made of different plugins.

## Plugins
### Register a plugin
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
        "pluginPath": "/myplugin",
        "scriptURL": "/url/to/the/plugin/source/file",
        "pluginTemplate": "<my-web-component custom-prop='foo'></my-web-component>"
        "subcomponents": {
            // Other plugin definition
        }
}
```  
The format of the json structure is validated by a json schema.

The `scriptURL` property provides an url to a valid web component.

### Notification in Admin Center
Each registered plugin can display message as a notification in the Admin Center.
It must trigger the event `ank-admin-notify` with the following parameter :

```
{
    content: {
        title: "Message title",
        message: "My message content"
    type: "admin-error", 
}
```

`title` is optionnal and `type` can be equal to `"admin-error"` or `"admin-success"`