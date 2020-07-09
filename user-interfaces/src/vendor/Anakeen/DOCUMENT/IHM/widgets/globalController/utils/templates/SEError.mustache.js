export default `<div class="dcpStaticErrorMessage" hidden>
    <style>
        .dcpStaticErrorMessage {
            text-align: center;
        }
    </style>
    <div class="alert alert-danger" role="alert">
        <h4>{{#msg}}{{msg.loadError}}{{/msg}}</h4>
        <button class="staticErrorReloadButton btn btn-default">{{msg.clickAction}}
        </button>
    </div>
</div>`;
