export default `# if (!iconClass && !text) {#
<a role="button" href="##" class="k-button k-grid-#: name#">
    #: name#
</a>
# } else if (!text) {#
<a role="button" href="##" class="k-button k-button-icon k-grid-#: name#">
    <span class="#: iconClass#"></span>
</a>
# } else if (!iconClass) {#
<a role="button" href="##" class="k-button k-grid-#: name#">
    <span class="action-label">#: text#</span>
</a>
# } else {#
<a role="button" href="##" class="k-button k-button-icontext k-grid-#: name#">
    <span class="#: iconClass#"></span>
    <span class="action-label">#: text#</span>
</a>
# } #`;