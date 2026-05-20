#!/bin/bash
# Jenkins initialization script for Docker

# This script runs inside the Jenkins container and sets up the initial configuration

cat > /var/jenkins_home/credentials.xml <<'EOF'
<?xml version='1.1' encoding='UTF-8'?>
<com.cloudbees.plugins.credentials.CredentialsProvider plugin="credentials@1206.v8a5e7a5f4faa">
  <domainCredentialsMap class="hudson.util.CopyOnWriteMap$Hash"/>
</com.cloudbees.plugins.credentials.CredentialsProvider>
EOF

# Create Jenkins configuration file
cat > /var/jenkins_home/jenkins.model.JenkinsLocationConfiguration.xml <<'EOF'
<?xml version='1.1' encoding='UTF-8'?>
<hudson.model.JenkinsLocationConfiguration>
  <adminAddress>Jenkins</adminAddress>
  <url>http://localhost:9090/</url>
</hudson.model.JenkinsLocationConfiguration>
EOF

echo "Jenkins configuration initialized"
