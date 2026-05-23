<?php

namespace App\Commands;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class CompletionCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'completion {shell : bash|zsh|fish}';
    protected $description = 'Generate shell completion script';

    public function handle(): int
    {
        $this->bootServices();
        $shell = $this->argument('shell');

        $scripts = [
            'bash' => $this->bashCompletion(),
            'zsh'  => $this->zshCompletion(),
            'fish' => $this->fishCompletion(),
        ];

        if (! isset($scripts[$shell])) {
            $this->fmt->error($this, 'Unsupported Shell', "'{$shell}' is not supported.", 'Use: bash, zsh, or fish');
            return self::FAILURE;
        }

        $this->line($scripts[$shell]);
        return self::SUCCESS;
    }

    protected function bashCompletion(): string
    {
        return <<<'BASH'
_sshelf() {
  local cur="${COMP_WORDS[COMP_CWORD]}"
  local cmds="auth:login auth:logout auth:status ping server:list server:get server:add server:edit server:delete key:list key:get key:add key:edit key:delete tag:list tag:get tag:add tag:edit tag:delete qc:list qc:get qc:add qc:edit qc:delete qc:run exec completion"

  if [[ ${COMP_CWORD} -eq 1 ]] ; then
    COMPREPLY=( $(compgen -W "${cmds}" -- ${cur}) )
    return 0
  fi
}
complete -F _sshelf sshelf
BASH;
    }

    protected function zshCompletion(): string
    {
        return <<<'ZSH'
#compdef sshelf

_sshelf() {
  local -a cmds
  cmds=(
    'auth:login:Authenticate with your Sshelf instance'
    'auth:logout:Remove stored credentials'
    'auth:status:Show current authentication status'
    'ping:Check connectivity to your Sshelf instance'
    'server:list:List all servers'
    'server:get:Show a single server'
    'server:add:Add a new server'
    'server:edit:Edit an existing server'
    'server:delete:Delete a server'
    'key:list:List all SSH keys'
    'key:add:Add a new SSH public key'
    'tag:list:List all tags'
    'tag:add:Add a new tag'
    'qc:list:List all quick commands'
    'qc:run:Run a saved quick command on its server'
    'exec:Execute a command on a remote server'
    'completion:Generate shell completion script'
  )

  _arguments '1:command:->command'
  
  case $state in
    command)
      _describe -t commands 'sshelf commands' cmds
    ;;
  esac
}

_sshelf "$@"
ZSH;
    }

    protected function fishCompletion(): string
    {
        return <<<'FISH'
complete -c sshelf -f
complete -c sshelf -n "__fish_use_subcommand" -a auth:login -d "Authenticate with your Sshelf instance"
complete -c sshelf -n "__fish_use_subcommand" -a auth:logout -d "Remove stored credentials"
complete -c sshelf -n "__fish_use_subcommand" -a auth:status -d "Show current authentication status"
complete -c sshelf -n "__fish_use_subcommand" -a ping -d "Check connectivity to your Sshelf instance"
complete -c sshelf -n "__fish_use_subcommand" -a server:list -d "List all servers"
complete -c sshelf -n "__fish_use_subcommand" -a server:get -d "Show a single server"
complete -c sshelf -n "__fish_use_subcommand" -a server:add -d "Add a new server"
complete -c sshelf -n "__fish_use_subcommand" -a server:edit -d "Edit an existing server"
complete -c sshelf -n "__fish_use_subcommand" -a server:delete -d "Delete a server"
complete -c sshelf -n "__fish_use_subcommand" -a key:list -d "List all SSH keys"
complete -c sshelf -n "__fish_use_subcommand" -a key:add -d "Add a new SSH public key"
complete -c sshelf -n "__fish_use_subcommand" -a tag:list -d "List all tags"
complete -c sshelf -n "__fish_use_subcommand" -a tag:add -d "Add a new tag"
complete -c sshelf -n "__fish_use_subcommand" -a qc:list -d "List all quick commands"
complete -c sshelf -n "__fish_use_subcommand" -a qc:run -d "Run a saved quick command on its server"
complete -c sshelf -n "__fish_use_subcommand" -a exec -d "Execute a command on a remote server"
complete -c sshelf -n "__fish_use_subcommand" -a completion -d "Generate shell completion script"
FISH;
    }
}
