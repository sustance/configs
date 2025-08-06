#!/bin/bash
read -p "Host c,D,g,H,S,T: " host
ssh -vD 9999 -C "$host"
exec bash
