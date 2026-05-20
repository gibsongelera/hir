pipeline {
    agent any
    
    environment {
        // Jenkins API Token for remote execution
        JENKINS_API_TOKEN = credentials('jenkins-api-token-8a8e5625aeba705a5205f55f5ac06717')
        DOCKER_IMAGE_NAME = 'hub-app'
        DOCKER_IMAGE_TAG = "${BUILD_NUMBER}"
        REGISTRY = 'localhost:5000'
        GIT_REPO = 'https://github.com/gibsongelera/hir.git'
    }
    
    options {
        buildDiscarder(logRotator(numToKeepStr: '10'))
        timeout(time: 1, unit: 'HOURS')
        disableConcurrentBuilds()
    }
    
    stages {
        stage('Checkout') {
            steps {
                script {
                    echo "🔄 Checking out code from GitHub..."
                    checkout([$class: 'GitSCM',
                        branches: [[name: '*/main']],
                        userRemoteConfigs: [[url: "${GIT_REPO}"]]
                    ])
                }
            }
        }
        
        stage('Build Docker Image') {
            steps {
                script {
                    echo "🐳 Building Docker image: ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}"
                    sh '''
                        docker build -t ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} .
                        docker tag ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} ${DOCKER_IMAGE_NAME}:latest
                    '''
                }
            }
        }
        
        stage('Push to Registry') {
            steps {
                script {
                    echo "📤 Pushing Docker image to registry..."
                    sh '''
                        docker push ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} || true
                    '''
                }
            }
        }
        
        stage('Stop Previous Container') {
            steps {
                script {
                    echo "⏹️  Stopping previous container..."
                    sh '''
                        docker stop hub-app-container 2>/dev/null || true
                        docker rm hub-app-container 2>/dev/null || true
                    '''
                }
            }
        }
        
        stage('Deploy') {
            steps {
                script {
                    echo "🚀 Deploying Docker container..."
                    sh '''
                        docker run -d \
                            --name hub-app-container \
                            -p 80:80 \
                            -e DATABASE_HOST=${DB_HOST:-localhost} \
                            -e DATABASE_USER=${DB_USER:-root} \
                            -e DATABASE_PASSWORD=${DB_PASSWORD:-} \
                            -e DATABASE_NAME=${DB_NAME:-campus_relief_hub} \
                            -v hub-app-uploads:/var/www/html/hub1/uploads \
                            ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}
                        
                        echo "✅ Container deployed successfully"
                        docker ps -f "name=hub-app-container"
                    '''
                }
            }
        }
        
        stage('Health Check') {
            steps {
                script {
                    echo "🏥 Performing health check..."
                    sh '''
                        sleep 5
                        if curl -f http://localhost/hub1/index.php > /dev/null 2>&1; then
                            echo "✅ Health check passed"
                        else
                            echo "⚠️  Health check warning - container may still be starting"
                        fi
                    '''
                }
            }
        }
    }
    
    post {
        always {
            script {
                echo "📋 Pipeline execution completed"
            }
        }
        success {
            script {
                echo "✅ Build and deployment successful!"
                // Optional: Send notification
                // mail to: 'your-email@example.com',
                //          subject: "Build #${BUILD_NUMBER} SUCCESS",
                //          body: "Build deployed successfully"
            }
        }
        failure {
            script {
                echo "❌ Build or deployment failed"
                // Optional: Send notification
                // mail to: 'your-email@example.com',
                //          subject: "Build #${BUILD_NUMBER} FAILED",
                //          body: "Build failed. Check logs for details"
            }
        }
    }
}
