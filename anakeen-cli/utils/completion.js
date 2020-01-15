const util = require("util");
const glob = util.promisify(require("glob"));
const fs = require("fs");
const fs_stat = util.promisify(fs.stat);
const path = require("path");
const Mustache = require("mustache");

const zshShell =
  (process.env.SHELL && process.env.SHELL.indexOf("zsh") !== -1) ||
  (process.env.ZSH_NAME && process.env.ZSH_NAME.indexOf("zsh") !== -1);

const completionShTemplate = `###-begin-{{{app_name}}}-completions-###
#
# yargs command completion script
#
# Installation: {{{app_call_name}}} {{{completion_command}}} >> ~/.bashrc
#    or {{{app_call_name}}} {{{completion_command}}} >> ~/.bash_profile on OSX.
#
_yargs_completions()
{
    local cur_word args type_list

    cur_word="\${COMP_WORDS[COMP_CWORD]}"
    args=("\${COMP_WORDS[@]}")

    # ask yargs to generate completions.
    type_list=$({{{app_path}}} --get-yargs-completions "\${args[@]}")

    COMPREPLY=( $(compgen -W "\${type_list}" -- \${cur_word}) )

    # if no match was found, fall back to filename completion
    if [ \${#COMPREPLY[@]} -eq 0 ]; then
      COMPREPLY=()
    fi

    return 0
}
complete -o default -F _yargs_completions {{{app_call_name}}}
###-end-{{{app_name}}}-completions-###
`;

const completionZshTemplate = `###-begin-{{{app_name}}}-completions-###
#
# yargs command completion script
#
# Installation: {{{app_call_name}}} {{{completion_command}}} >> ~/.zshrc
#    or {{{app_call_name}}} {{{completion_command}}} >> ~/.zsh_profile on OSX.
#
_{{{app_name}}}_yargs_completions()
{
  local reply
  local si=$IFS
  IFS=$'\n' reply=($(COMP_CWORD="$((CURRENT-1))" COMP_LINE="$BUFFER" COMP_POINT="$CURSOR" {{{app_path}}} --get-yargs-completions "\${words[@]}"))
  IFS=$si
  _describe 'values' reply
}
compdef _{{{app_name}}}_yargs_completions {{{app_call_name}}}
###-end-{{{app_name}}}-completions-###
`;

exports.isZshShell = () => {
  return zshShell;
};

exports.generateCompletionScript = () => {
  if (zshShell) {
    return Mustache.render(completionZshTemplate, {
      app_name: "anakeen-cli",
      app_path: "npx @anakeen/anakeen-cli-completion",
      completion_command: "completion",
      app_call_name: `npx @anakeen/anakeen-cli`
    });
  }
  return Mustache.render(completionShTemplate, {
    app_name: "anakeen-cli",
    app_path: "npx @anakeen/anakeen-cli-completion",
    completion_command: "completion",
    app_call_name: `node_modules/`
  });
};

exports.analyzePathForCommand = async (currentPath, filter = "", onlyCommand = false) => {
  const result = await glob(`${currentPath}/*.js`);
  return result.reduce((acc, currentElement) => {
    const commandName = path.basename(currentElement, ".js");
    if (filter) {
      if (commandName.indexOf(filter) !== 0) {
        return acc;
      }
    }
    if (!zshShell || onlyCommand) {
      acc.push(commandName);
      return acc;
    }
    const currentFile = path.resolve(currentElement);
    const currentCommand = require(currentFile);
    acc.push(`${commandName.replace(/:/g, "\\:")}:${currentCommand.desc || currentCommand.description || ""}`);
    return acc;
  }, []);
};

exports.analyzeFileForCommand = async currentFile => {
  await fs_stat(currentFile);
  const currentCommand = require(currentFile);
  const builder = currentCommand.builder;
  if (!builder) {
    return [];
  }
  return Object.keys(builder).map(currentBuilderKey => {
    if (zshShell) {
      const commandDescription = builder[currentBuilderKey].description || "";
      return `--${currentBuilderKey.replace(/:/g, "\\:")}:${commandDescription.replace("__yargsString__:", "")}`;
    }
    return `--${currentBuilderKey}`;
  });
};
