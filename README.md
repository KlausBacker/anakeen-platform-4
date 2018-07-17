# Anakeen Admin Center

Web interfaces to admin applications and configure smart elements

Admin Center is made of different plugins.

## Plugins
### Register a plugin
A plugin is declared in the directory `src/config/adminPlugins` as a json file.
The structure of the xml file must respect the following format :

```
<admin:config xmlns:admin="http://www.anakeen.com/ns/admin/">
    <admin:plugins>
        <admin:plugin name="MyPluginName">
            <admin:title>Plugin Title</admin:title>
            <admin:icon><![CDATA[<i class='my-icon'></i>]]></admin:icon>
            <admin:pluginPath>/myplugin</admin:pluginPath>
            <admin:scriptURL>/url/to/the/plugin/source/file</admin:scriptURL>
            <admin:debugScriptURL>/AdminCenter/debug/ank-admin-account.js</admin:debugScriptURL>
            <admin:pluginTemplate><![CDATA[<my-web-component custom-prop='foo'></my-web-component>]]></admin:pluginTemplate>
            <admin:order position="first"/>
        </admin:plugin>
    </admin:plugins>
</admin:config>
```  
The format of the xml structure is validated by a [xsd schema](ide/userAdminPlugin.xsd).

The `scriptURL` property provides an url to a valid web component.

### Overload an existing plugin

An existing plugin can be overrided. The override configuration is similar to route override definition.

```
<admin:config xmlns:admin="http://www.anakeen.com/ns/admin/">
    <admin:plugins>
        <admin:plugin name="MyPluginName" override="partial">
            <admin:title>Another Plugin title</admin:title>
            <admin:priority>100</admin:priority>
        </admin:plugin>
    </admin:plugins>
</admin:config>
```  

The `override` attribute can be equal to :

* `partial`
: The base plugin is overload by the new definition. The plugin keeps its "non overloaded" properties.

* `complete`
: The base plugin definition is totally ignored the new definition replace the old one.

If several plugin overloads are provided for the same plugin, you should speficy the `priority` tag.
The overloads will be applied by order of `priority` value. 

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

### Plugin Hooks

The plugin can self-register a hook function to bind the show/hide plugin visibility 
in the Admin Center. These functions are only available on the top level element that represent the plugin.

**On plugin show**
```javascript
myPlugin.onpluginshow = function (element) {
    console.log("The plugin dom element : ", element);
}
```

**On plugin hidden**
```javascript
myPlugin.onpluginhidden = function (element) {
    console.log("The plugin dom element : ", element);
}
```

NB: If VueJS and vue-custom-element are used to build the plugin web component, Vue-like hooks lifecycle (such as `mounted`, `updated`) can be used.

Example : VueJS Plugin 
```html
<template>
    <div></div>
</template>

<script>
export default {
    mounted() {
        // Vuejs hook lifecycle
        console.log("The plugin is mounted");
    },
    
    // Only available on the plugin top level component
    pluginShow() {
        console.log("The plugin is shown");
    },
    
    // Only available on the plugin top level component
    pluginHidden() {
        console.log("The plugin is hidden");
    }
}
</script>
```