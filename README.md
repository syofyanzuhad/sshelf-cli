# Sshelf CLI

> Elegant command-line interface for managing your Sshelf instance.

Sshelf CLI allows you to manage your servers, SSH keys, tags, and execute remote commands directly from your terminal.

---

## Installation

### Prerequisites
- PHP 8.2 or higher

### Local Setup
1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/sshelf-cli.git
   cd sshelf-cli
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Use the `sshelf` binary:
   ```bash
   ./sshelf --version
   ```

### Global Installation (Optional)
To use `sshelf` from anywhere, create a symlink:
```bash
sudo ln -s "$(pwd)/sshelf" /usr/local/bin/sshelf
```

---

## Getting Started

### 1. Authentication
Login to your Sshelf instance using your API URL and Bearer Token:
```bash
sshelf auth:login
```

Check your connection status:
```bash
sshelf ping
```

### 2. Manage Servers
List all your servers:
```bash
sshelf server:list
```

Add a new server:
```bash
sshelf server:add --name="Web-01" --host="1.2.3.4" --username="root"
```

### 3. Remote Execution
Execute a command on a remote server by ID:
```bash
sshelf exec 1 "uptime"
```

Run a saved Quick Command:
```bash
sshelf qc:run 5
```

---

## Core Commands

| Command | Description |
| :--- | :--- |
| `auth:login` | Authenticate with your Sshelf instance |
| `auth:status` | Show current authentication status |
| `ping` | Check connectivity to your Sshelf instance |
| `server:list` | List all servers (supports `--json`, `--group`) |
| `server:add` | Add a new server |
| `exec` | Execute a command on a remote server |
| `qc:run` | Run a saved quick command |
| `key:list` | List all SSH keys |
| `tag:list` | List all tags |
| `completion` | Generate shell completion scripts |

---

## Shell Completion
Generate completion scripts for your favorite shell:

```bash
# Zsh
sshelf completion zsh > /usr/local/share/zsh/site-functions/_sshelf

# Bash
sshelf completion bash >> ~/.bashrc
```

---

## License
Sshelf CLI is open-source software licensed under the MIT license.
