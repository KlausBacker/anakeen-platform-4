export default `<a class="k-button k-button-icon k-grid-_subcommands">
    <ul class="actionMenu">
        <li><i class="#: iconClass#"></i>
            <ul class="subactionsItems">
                # for (var i = 0; i < subCommands.length; i++) { #
                    <li class="#:subCommands[i].className#" data-actionType="#: subCommands[i].name#">
                        # if (!subCommands[i].iconClass && !subCommands[i].text) {#
                        <button role="button" href="##" class="k-button k-grid-#: subCommands[i].name#">
                            #: subCommands[i].name#
                        </button>
                        # } else if (!subCommands[i].text) {#
                        <button role="button" href="##" class="k-button k-button-icon k-grid-#: subCommands[i].name#">
                            <span class="#: subCommands[i].iconClass#"></span>
                        </button>
                        # } else {#
                        <button role="button" href="##" class="k-button k-button-icontext k-grid-#: subCommands[i].name#">
                            <span class="#: subCommands[i].iconClass#"></span>
                            <span class="action-label">#: subCommands[i].text#</span>
                        </button>
                        # } #
                    </li>
                # } #
            </ul>
        </li>
    </ul>
</a>`;