# Copy Public SSH Key Alfred Workflow

This Alfred workflow lists your public SSH keys so you can quickly copy one to your clipboard for that thing you’re doing.

![Workflow screenshot](resources/screenshot.png)

Custom theme using [Berkeley Mono](https://berkeleygraphics.com/typefaces/berkeley-mono/).

## Installation

Download the `.alfredworkflow` file from the [latest release](https://github.com/mattstein/alfred-copy-ssh-workflow/releases) and double-click to install.

## Configuring

You shouldn’t need to configure anything unless you have a custom key directory or files you’d like to ignore.

Set optional environment from the **Workflows** settings, **Copy Public SSH Key**, then **[x]** at the top right, and finally the **Environment Variables** tab.


| Variable | Default | Required? | Note |
|---------------------| --- | --- | --- |
| `KEY_DIR` | `~/.ssh` | ❌ | |
| `IGNORE` | `.,..,.DS_Store,authorized_keys,config,known_hosts` | ❌ | Comma-separated list of files that should not be included in options. |

## Usage

Use the Alfred trigger `copyssh` to list your public keys. You can keep typing an argument to narrow the results.

Press <kbd>return</kbd> to copy the selected public key to your clipboard.
