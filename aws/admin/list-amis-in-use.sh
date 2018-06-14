#!/bin/bash

aws ec2 describe-instances --query 'Reservations[*].Instances[*].ImageId' --output text | xargs aws ec2 describe-images --query 'Images[*].[ImageId,Description]' --output text --image-ids
