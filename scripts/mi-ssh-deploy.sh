#!/usr/bin/env bash

# Deploy MediaIdeya through SSH/rsync. Default mode is a non-mutating dry run.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${DEPLOY_ENV_FILE:-$ROOT/.env.deploy}"

usage() {
  cat <<'USAGE'
Usage: ./scripts/mi-ssh-deploy.sh [--apply] [--delete]

  --apply   Upload files. Without this option the command is a dry run.
  --delete  Include files absent locally in the deletion plan.
USAGE
}

apply=false
delete=false

for argument in "$@"; do
  case "$argument" in
    --apply)
      apply=true
      ;;
    --delete)
      delete=true
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      printf 'Unknown option: %s\n\n' "$argument" >&2
      usage >&2
      exit 2
      ;;
  esac
done

if [[ ! -f "$ENV_FILE" ]]; then
  printf 'Deployment config not found: %s\n' "$ENV_FILE" >&2
  exit 1
fi

# shellcheck disable=SC1090
source "$ENV_FILE"

required=(
  DEPLOY_SSH_HOST
  DEPLOY_SSH_PORT
  DEPLOY_SSH_USER
  DEPLOY_SSH_IDENTITY_FILE
  DEPLOY_REMOTE_PATH
)

for variable_name in "${required[@]}"; do
  if [[ -z "${!variable_name:-}" ]]; then
    printf 'Missing %s in %s\n' "$variable_name" "$ENV_FILE" >&2
    exit 1
  fi
done

if [[ ! "$DEPLOY_SSH_PORT" =~ ^[0-9]+$ ]]; then
  printf 'DEPLOY_SSH_PORT must be numeric.\n' >&2
  exit 1
fi

if [[ ! -r "$DEPLOY_SSH_IDENTITY_FILE" ]]; then
  printf 'SSH private key is not readable: %s\n' "$DEPLOY_SSH_IDENTITY_FILE" >&2
  exit 1
fi

ssh_command=(
  ssh
  -i "$DEPLOY_SSH_IDENTITY_FILE"
  -p "$DEPLOY_SSH_PORT"
  -o IdentitiesOnly=yes
  -o StrictHostKeyChecking=accept-new
)

remote_target="${DEPLOY_SSH_USER}@${DEPLOY_SSH_HOST}"
printf -v remote_path_shell_escaped '%q' "$DEPLOY_REMOTE_PATH"

printf 'Checking SSH access to %s …\n' "$remote_target"
"${ssh_command[@]}" "$remote_target" "test -d $remote_path_shell_escaped && command -v rsync >/dev/null"

printf -v rsync_shell '%q ' "${ssh_command[@]}"

rsync_options=(
  --archive
  --compress
  --human-readable
  --itemize-changes
  --exclude=.git/
  --exclude=.DS_Store
  --exclude=.codemie/
  --exclude=.agent-tools/
  --exclude=.cursor/
  --exclude=.codebase-memory/
  --exclude=.figma-cache/
  --exclude=.feedback/
  --exclude=.tunnel/
  --exclude=.env
  --exclude=.env.*
  --exclude=AGENTS.md
  --exclude=docs/
  --exclude=scripts/
  --exclude=backup/
  --exclude=templates/_extract/
  --exclude=engine/cache/
  --exclude=engine/data/config.php
  --exclude=engine/data/dbconfig.php
  --exclude=.well-known/
  --exclude=cgi-bin/
  --exclude=.user.ini
  --exclude=php.ini
)

if [[ "$apply" != true ]]; then
  printf '%s\n' 'Dry run only: no remote files will be changed.'
  rsync_options+=(--dry-run)
fi

if [[ "$delete" == true ]]; then
  if [[ "$apply" == true ]]; then
    printf '%s\n' 'Delete mode enabled: remote site files absent locally will be removed.'
  else
    printf '%s\n' 'Delete plan enabled: dry run will show remote files that would be removed.'
  fi
  rsync_options+=(--delete --delete-delay)
fi

rsync "${rsync_options[@]}" \
  -e "$rsync_shell" \
  "$ROOT/" \
  "${remote_target}:${DEPLOY_REMOTE_PATH}/"

if [[ "$apply" == true ]]; then
  printf '%s\n' 'MediaIdeya SSH deployment completed.'
else
  printf '%s\n' 'Dry run completed. Re-run with --apply when ready.'
fi
