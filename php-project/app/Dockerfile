# Step 1: Use an official PHP runtime as a base image
FROM php:7.4-cli

# Step 2: Install necessary extensions (PDO MySQL)
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql

# Step 3: Set the working directory inside the container
WORKDIR /app

# Step 4: Copy the current directory contents into the container at /app
COPY . /app

# Step 5: Expose port 5000
EXPOSE 5000

# Step 6: Start PHP's built-in server
CMD ["php", "-S", "0.0.0.0:5000", "app.php"]
