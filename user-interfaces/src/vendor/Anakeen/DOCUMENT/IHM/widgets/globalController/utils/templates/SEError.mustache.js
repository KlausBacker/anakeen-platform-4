export default `<div class="dcpStaticErrorMessage" hidden>
    <style>
        .dcpStaticErrorMessage {
            text-align: center;
            z-index: 10000;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }
    </style>
    <div class="alert alert-danger" role="alert">
        <h4>{{#msg}}{{msg.loadError}}{{/msg}}</h4>
        <button class="staticErrorReloadButton btn btn-default">{{msg.clickAction}}
        </button>
    </div>
</div>`;