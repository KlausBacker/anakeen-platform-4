# Basic usage guide for Keycloak

This a basic usage guide for setting up a custom Realm to be used with OpenID
Connect by Anakeen Platform.

## Login as admin

The keycloak service is accessible on http://localhost:8083/ with the following
credentials:
- username: `admin`
- password: `anakeen`

## Create a new Realm

- Hover the top dropdown menu and select "Add realm".
- Fill-in the `Name` (e.g. "ACMECorp"), then click `[Create]`.
- Your new realm is accessible in the top dropdown menu.

## Configure new Realm

- Select the `ACMECorp` realm in the top dropdown menu.
- In the `General` tab, set `Frontend URL` with the URL of the realm as it will
  be used by the end-user (e.g. `http://localhost:8083/auth`). This is for the
  case when the user access to the portal is on a hostname that is not the same
  as the one used by Anakeen Platform to access the portal. This `Frontend URL`
  must then be set with the hostname that will be used by the end-user.
  This is typically the case in a docker-compose, where the Anakeen Platform
  server will access the portal with the hostname declared in the
  docker-compsoe's network, whereas the end-user will use the docker-compose
  exposed ports on a localhost URL.

## Create a new client for your realm

- Select the `ACMECorp` realm in the top dropdown menu.
- Click on `Clients`.
- Click the `[Create]` button.
- Fill-in the `Client ID` field (e.g. `ap4`).
- Set `Client Protocol` to `openid-connect`.
- Click the `[Save]` button.

Your client has been created and is now displayed.

- Go to the `Settings` tab of your client.
- Set `Access Type` to `confidential`.
- Add your Anakeen Platform server's URLs in `Valid Redirect URLs`:
  - `http://localhost:8080/*`
  - `http://localhost:8443/*`
- Click the `[Save]` button.

Your client refreshes and displays a new `Credentials` tab that contains the
`client Secret` string to be used by the AP4 client.

## Parameters for OpenID Connect client

The parameters for authenticating against this realm/portal from an OpenID
Connect client are:
- `clientId`: `ap4`
- `clientSecret`: The secret from the client's `Credentials` tab seen above.
- The list of the various OpenID Connect endpoints is available in
  `Realm Settings` > `General` > `Endpoints` > `OpenID Endpoint Configuration`.

# Administrative tasks

References:
* https://github.com/keycloak/keycloak-containers/blob/master/server/README.md
* https://www.keycloak.org/docs/latest/server_admin/index.html#_export_import

## Export configuration

To export the configuration (in "dir" format) to
`.devtools/docker/Volumes/_private/data/export-import`:

```shell script
$ make keycloak-export-config
````

The export will first stop the current container, then perform the export, and
finally restart the container.

If, for any reasons, the restart should fail, then you can manually force a
start with:

```shell script
$ make keycloak-start
```

## Import configuration ("dir" format)

To import a configuration (in "dir" format) from
`.devtools/docker/Volumes/_private/data/export-import`:

```shell script
$ make keycloak-import-config
```

The import will first stop the current container, then perform the import, and
finally restart the container.

If, for any reasons, the restart should fail, then you can manually force a
start with:

```shell script
$ make keycloak-start
```
