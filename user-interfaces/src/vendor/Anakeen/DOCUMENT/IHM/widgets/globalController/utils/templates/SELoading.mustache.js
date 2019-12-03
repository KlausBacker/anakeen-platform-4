export default `<div class="dcpLoading">
    <style>
        .white-logo .main-letter {
            fill: #93C648;
        }

        .white-logo .letter {
            fill: #FFF;
        }

        .white-logo .st0 {
            fill: #FFF;
            stroke: #FFF;
            stroke-width: 0.25;
            stroke-miterlimit: 10;
        }

        .white-logo .moving-path {
            background: linear-gradient(to right, rgba(221, 221, 221, 0) 0%, #dddddd 20%, #dddddd 100%);
        }

        .black-logo .main-letter {
            fill: #93C648;
        }

        .black-logo .letter {
            fill: #263438;
        }

        .black-logo .st0 {
            fill: #263438;
            stroke: #263438;
            stroke-width: 0.25;
            stroke-miterlimit: 10;
        }

        .black-logo .moving-path {
            background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, white 20%, white 100%);
        }

        .black-logo.isotype path.line {
            stroke: #000;
        }

        .dcpLoading--logo {
            width: 20%;
            min-width: 20rem;
            padding: 3em;
            overflow: hidden;
            margin-top: 10rem;
        }

        .dcpLoading--logo .text-logo, .dcpLoading--logo .main-logo {
            margin: 0 auto;
            width: 100%;
        }

        .dcpLoading--logo .text-logo svg, .dcpLoading--logo .main-logo svg {
            overflow: visible;
            width: 100%;
        }

        .dcpLoading--logo .text-logo _:-ms-input-placeholder, :root .dcpLoading--logo .text-logo {
            margin-bottom: -2.2rem;
        }

        .dcpLoading--logo.isotype {
            height: 20%;
        }

        .dcpLoading--logo.isotype .main-logo {
            height: 100%;
        }

        .dcpLoading--logo.isotype .main-logo svg.bounce {
            width: 20%;
            margin: 0 auto;
            display: block;
        }

        .dcpLoading--logo.isotype .main-logo svg.bounce > * {
            animation: bounce-isotype infinite 800ms alternate ease-in-out;
        }

        .main-logo {
            position: relative;
        }

        .main-logo svg.bounce > * {
            animation: bounce infinite 2500ms normal ease-in-out;
            opacity: 1;
        }

        .main-logo svg.bounce > *:nth-child(1) {
            animation-delay: 0ms;
        }

        .main-logo svg.bounce > *:nth-child(2) {
            animation-delay: 50ms;
        }

        .main-logo svg.bounce > *:nth-child(3) {
            animation-delay: 100ms;
        }

        .main-logo svg.bounce > *:nth-child(4) {
            animation-delay: 150ms;
        }

        .main-logo svg.bounce > *:nth-child(5) {
            animation-delay: 200ms;
        }

        .main-logo svg.bounce > *:nth-child(6) {
            animation-delay: 250ms;
        }

        .main-logo svg.bounce > *:nth-child(7) {
            animation-delay: 300ms;
        }

        .main-logo .moving-path {
            opacity: 0;
            width: 130%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            transform: translate(100%, 0);
            animation: slide-right infinite 2500ms normal ease-in;
        }

        @keyframes slide-right {
            0% {
                opacity: 0;
                transform: translate(-20%, 0);
            }
            35% {
                opacity: 0;
                transform: translate(-20%, 0);
            }
            50% {
                opacity: 1;
                transform: translate(-20%, 0);
            }
            75% {
                transform: translate(100%, 0);
            }
            100% {
                opacity: 1;
            }
        }

        @keyframes bounce {
            0% {
                transform: scale(1);
            }
            70% {
                transform: scale(1);
                opacity: 1;
            }
            85% {
                transform: scale(1.1);
                opacity: 0.5;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes bounce-isotype {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            60% {
                transform: scale(1);
                opacity: 1;
            }
            100% {
                transform: scale(1.1);
                opacity: 0.5;
            }
        }

        .dcpLoading--content {
            background-color: #FFF;
            width: 100%;
            display: flex;
            flex-grow: 1;
            justify-content: center;
        }

        .dcpLoading--content + .dcpLoading--content {
            margin-top: 2px;
        }

        .dcpLoading {
            padding: 0;
            border: none;
            text-align: center;
            position: absolute;
            display:flex;
            flex-direction: column;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: 0;
            line-height: 1px;
            z-index: 11;
            background-color: white;
        }

        .dcpLoading .progress {
            width: 100%;
            height: 3px;
            box-sizing: border-box;
            border: none;
            margin: 0;
            background: $body-bg;
        }

        .dcpLoading .progress .progress-bar {
            background-color: #8AE234;
        }
    </style>
    <div class="progress active dcpLoading--progressbar">
        <div class="progress-bar progress-bar-striped" style="width: 10%"></div>
    </div>
    <div class="dcpLoading--content">
        <div class="dcpLoading--logo black-logo">
            <div class="text-logo">
                <svg xmlns="http://www.w3.org/2000/svg" id="poweredby" x="0" y="0" version="1.1"
                     viewBox="0 0 580.8 99.1" xml:space="preserve"><path d="M115.5 34.1H99.7v18.5h-2.6V2.2h18.4c5.2 0 9 1.3 11.3 3.8 2.4 2.5 3.6 6.4 3.6 11.7 0 10.9-5 16.4-14.9 16.4zm-15.8-2.5h15.8c8.1 0 12.2-4.7 12.2-14 0-4.4-1-7.7-2.9-9.8-1.9-2.1-5-3.2-9.3-3.2H99.7v27zM145.9 19.9c2.2-2.7 6-4 11.3-4s9.1 1.3 11.3 4c2.2 2.7 3.3 7.5 3.3 14.5s-1 11.9-3 14.6c-2 2.7-5.9 4.1-11.7 4.1-5.8 0-9.7-1.4-11.7-4.1-2-2.7-3-7.6-3-14.6.1-6.9 1.2-11.8 3.5-14.5zm4.9 30.2c1.6.6 3.7.8 6.4.8s4.8-.3 6.4-.8c1.6-.6 2.8-1.6 3.6-3.1.8-1.5 1.4-3.2 1.6-5.1.2-1.9.4-4.5.4-8 0-6-.8-10.2-2.5-12.4-1.7-2.2-4.8-3.3-9.4-3.3s-7.8 1.1-9.4 3.3c-1.7 2.2-2.5 6.3-2.5 12.4 0 3.4.1 6.1.4 8 .2 1.9.8 3.6 1.6 5.1.6 1.5 1.8 2.5 3.4 3.1zM183.4 16.6h2.7l9.1 33.7h.8l10.7-33h2.7l10.7 33h.8l9.1-33.7h2.7l-9.7 36h-4.9l-10-32.3-10 32.3h-4.9l-9.8-36zM268.8 50.4l2.2-.1.1 2.3c-5.4.4-9.8.6-13.3.6-5.1 0-8.6-1.6-10.6-4.6-2-3-3-7.7-3-14 0-12.4 4.8-18.6 14.5-18.6 4.6 0 8 1.4 10.3 4.1 2.3 2.7 3.4 7.1 3.4 13.2v2.4h-25.6c0 5.2.8 9 2.4 11.6 1.6 2.5 4.3 3.8 8.1 3.8 3.9-.2 7.7-.4 11.5-.7zm-21.9-17.1h22.9c0-5.4-.9-9.3-2.6-11.6-1.8-2.3-4.5-3.5-8.4-3.5-8 0-11.9 5-11.9 15.1zM287.3 52.6v-36h2.5v5.6c1.4-1.2 3.6-2.4 6.4-3.7 2.9-1.3 5.5-2.2 7.8-2.6v2.6c-2.1.4-4.4 1.2-6.7 2.2-2.4 1-4.2 1.9-5.5 2.7l-2 1.2v28h-2.5zM338.1 50.4l2.2-.1.1 2.3c-5.4.4-9.8.6-13.3.6-5.1 0-8.6-1.6-10.6-4.6-2-3-3-7.7-3-14 0-12.4 4.8-18.6 14.5-18.6 4.6 0 8 1.4 10.3 4.1 2.3 2.7 3.4 7.1 3.4 13.2v2.4h-25.6c0 5.2.8 9 2.4 11.6 1.6 2.5 4.3 3.8 8.1 3.8 4-.2 7.7-.4 11.5-.7zm-21.9-17.1h22.9c0-5.4-.9-9.3-2.6-11.6-1.8-2.3-4.5-3.5-8.4-3.5-7.9 0-11.9 5-11.9 15.1zM382.3 0v52.6h-2.5v-3c-1.4.9-3.3 1.8-5.8 2.5-2.5.8-4.5 1.2-6.1 1.2-1.6 0-2.8-.1-3.6-.2-.8-.1-1.9-.5-3.1-1.2s-2.3-1.6-3.1-2.8c-.8-1.2-1.5-3-2.1-5.4-.6-2.4-.9-5.3-.9-8.6 0-6.5 1.1-11.4 3.3-14.5 2.2-3.1 6.2-4.7 11.8-4.7 2.7 0 5.9.3 9.5.9V0h2.6zM364 50.4c.9.3 2.2.5 3.7.5s3.3-.3 5.4-.9c2.1-.6 3.7-1.2 4.9-1.9l1.7-.9V19.1c-3.7-.6-6.9-.9-9.5-.9-4.8 0-8.1 1.4-9.9 4.1-1.8 2.7-2.6 7-2.6 12.8 0 6.9 1.1 11.4 3.3 13.4 1.1 1 2.1 1.6 3 1.9zM422.8 18.8c4-1.9 8.2-2.9 12.7-2.9s7.6 1.4 9.3 4.2c1.7 2.8 2.6 7.6 2.6 14.3S446.3 46 444 48.9c-2.3 2.9-6.6 4.3-13 4.3-3.4 0-6.4-.2-9.1-.5l-1.6-.1V0h2.5v18.8zm0 31.7c3.7.3 6.8.4 9.1.4s4.4-.3 6.3-.9c1.8-.6 3.3-1.7 4.2-3.2 1-1.5 1.6-3.3 1.9-5.1.3-1.9.5-4.4.5-7.6 0-5.7-.6-9.7-1.9-12.1-1.3-2.4-3.8-3.6-7.6-3.6-1.9 0-3.9.2-6 .7-2.1.5-3.6 1-4.8 1.4l-1.7.7v29.3zM458.6 16.6h2.7l10.6 33.7h3l10.7-33.7h2.7l-16.5 52.6H469l5.2-16.6h-4.4l-11.2-36z" class="st0"/></svg>
            </div>
            <div class="main-logo">
                <svg xmlns="http://www.w3.org/2000/svg" id="logo" x="0" y="0" class="bounce"
                     preserveAspectRatio="xMidYMin" version="1.1" viewBox="0 0 580.8 119.3" xml:space="preserve"><path id="A1" d="M42.1 43.6c17.8 0 28.4 13.7 28.5 30.5.1 17-9.6 32-28.1 32-18 0-27.7-15.6-27.7-32.1-.1-15.9 10.6-30.4 27.3-30.4m-1.6-13.3C15.1 30.3-.1 50.9 0 75c.1 23.3 15.8 44.3 40.6 44.3 11.9 0 21.7-4.6 29-14h.4v11.6h14.4l-.3-84.3H69.7l.1 12h-.4c-7-8.9-17.4-14.3-28.9-14.3" class="main-letter"/>
                    <path id="N1"
                          d="M135.1 30.3c-10.4 0-18.1 4.5-24 12.6h-.4V32.7H96.3l.3 84.4H111l-.1-40c-.1-15.1 1.1-33.4 20.9-33.4 16.7 0 17.8 12.2 17.9 25.8l.2 47.6h14.4l-.3-50.2c-.1-19.7-5.5-36.6-28.9-36.6"
                          class="letter"/>
                    <path id="A2"
                          d="M215.4 43.6c17.8 0 28.4 13.7 28.5 30.5.1 17-9.6 32-28.1 32-18 0-27.7-15.6-27.8-32.1 0-15.9 10.7-30.4 27.4-30.4m-1.6-13.3c-25.3 0-40.5 20.7-40.4 44.7.1 23.3 15.8 44.3 40.5 44.3 11.9 0 21.7-4.6 29.1-14h.4v11.6h14.4l-.3-84.3h-14.4v12h-.4c-7-8.9-17.4-14.3-28.9-14.3"
                          class="letter"/>
                    <path id="K"
                          d="M336.8 32.6h-19.2L287 63.9 286.8 0h-14.4l.4 117h14.4l-.1-34.7 3.7-3.7 34.1 38.4h19l-43.1-48.5z"
                          class="letter"/>
                    <path id="E1"
                          d="M376.9 43.6c12.6 0 22.9 9.9 24.9 22.1h-50.5c1.8-12.3 13.2-22.1 25.6-22.1m-.2-13.3c-26.4 0-40.9 20.7-40.8 45.6.1 24.2 16.3 43.4 41.4 43.4 17.2 0 30.9-8.6 38.9-23.7l-12.2-7c-5.5 10-13.4 17.4-25.6 17.4-16.3 0-27.5-12.6-27.7-28.2H417c1.3-25.1-13.2-47.5-40.3-47.5"
                          class="letter"/>
                    <path id="E2"
                          d="M462.9 43.6c12.6 0 22.9 9.9 24.9 22.1h-50.5c1.8-12.3 13.2-22.1 25.6-22.1m-.2-13.3c-26.4 0-40.9 20.7-40.8 45.6.1 24.2 16.3 43.4 41.4 43.4 17.2 0 30.9-8.6 38.9-23.7l-12.2-7c-5.5 10-13.4 17.4-25.6 17.4-16.3 0-27.5-12.6-27.7-28.2H503c1.3-25.1-13.2-47.5-40.3-47.5"
                          class="letter"/>
                    <path id="N2"
                          d="M550.6 30.3c-10.4 0-18.1 4.5-24 12.6h-.4V32.7h-14.4l.3 84.4h14.4l-.1-40c-.1-15.1 1.1-33.4 20.9-33.4 16.7 0 17.8 12.2 17.9 25.8l.2 47.6h14.4l-.2-50.1c-.2-19.8-5.7-36.7-29-36.7"
                          class="letter"/></svg>
                <div class="moving-path"></div>
            </div>
        </div>
    </div>
</div>`;