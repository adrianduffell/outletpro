#!/bin/bash
INPUT=$(cat)
TOOL_NAME=$(echo "$INPUT" | jq -r '.toolName')

if [ "$TOOL_NAME" != "bash" ]; then
  exit 0
fi

TOOL_ARGS=$(echo "$INPUT" | jq -r '.toolArgs')
COMMAND=$(echo "$TOOL_ARGS" | jq -r '.command // empty' 2>/dev/null)

if ! echo "$COMMAND" | grep -q 'git commit'; then
  exit 0
fi

npm run lint:css && npm run lint:js && npm run lint:php || {
  echo '{"permissionDecision":"deny","permissionDecisionReason":"Lint failed. Fix lint errors before committing."}'
}
