#!/bin/sh
# Test sendmail: saves the message passed by PHP mail() and the arguments.
echo "$@" > "$CAPTURE_DIR/args"
cat > "$CAPTURE_DIR/message"
