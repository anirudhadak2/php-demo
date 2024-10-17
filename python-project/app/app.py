from flask import Flask, render_template, request, redirect, url_for, flash, session
import os
import sqlite3
import subprocess
from werkzeug.security import generate_password_hash, check_password_hash

app = Flask(__name__)
app.secret_key = 'your_secret_key'

# Ensure master folder exists
if not os.path.exists('master'):
    os.makedirs('master')

# Database setup function
def init_db():
    conn = sqlite3.connect('users.db')
    cursor = conn.cursor()
    cursor.execute('''CREATE TABLE IF NOT EXISTS users
                      (id INTEGER PRIMARY KEY AUTOINCREMENT,
                      username TEXT NOT NULL UNIQUE,
                      password TEXT NOT NULL)''')
    conn.commit()
    conn.close()

# Initialize DB
init_db()

# Default route - redirect to the registration page first
@app.route('/')
def index():
    return redirect(url_for('register'))  # Redirect to registration by default

# Register route
@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form['username']
        password = generate_password_hash(request.form['password'])

        # Database operations
        conn = sqlite3.connect('users.db')
        cursor = conn.cursor()

        try:
            # Insert new user into the database
            cursor.execute('INSERT INTO users (username, password) VALUES (?, ?)', (username, password))
            conn.commit()
            # Create user folder inside /master
            user_folder = os.path.join('master', username)
            os.makedirs(user_folder)
            # Copy sample scripts into the new user folder
            subprocess.run(['cp', 'scripts/sample_script.py', user_folder])
            flash(f'User {username} registered successfully. Please log in.', 'success')
            return redirect(url_for('login'))  # Redirect to login after successful registration
        except sqlite3.IntegrityError:
            flash('Username already taken. Try another.', 'danger')
        finally:
            conn.close()

    return render_template('register.html')

# Login route
@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']

        # Fetch user data from the database
        conn = sqlite3.connect('users.db')
        cursor = conn.cursor()
        cursor.execute('SELECT * FROM users WHERE username = ?', (username,))
        user = cursor.fetchone()
        conn.close()

        if user and check_password_hash(user[2], password):
            session['username'] = username
            flash(f'Welcome, {username}!', 'success')
            return redirect(url_for('user_home'))
        else:
            flash('Invalid credentials, please try again.', 'danger')

    return render_template('login.html')

# User home route
@app.route('/user_home')
def user_home():
    if 'username' not in session:
        flash('Please log in first.', 'warning')
        return redirect(url_for('login'))

    username = session['username']
    user_folder = os.path.join('master', username)

    # List Python scripts in the user's folder
    scripts = [f for f in os.listdir(user_folder) if f.endswith('.py')]

    return render_template('user_home.html', scripts=scripts, username=username)

# Execute script
@app.route('/run-script', methods=['POST'])
def run_script():
    if 'username' not in session:
        flash('You need to log in first.', 'warning')
        return redirect(url_for('login'))

    username = session['username']
    script_name = request.form['script_name']
    user_folder = os.path.join('master', username)
    script_path = os.path.join(user_folder, script_name)

    if os.path.exists(script_path):
        result = subprocess.run(['python3', script_path], capture_output=True, text=True)
        return f"<pre>{result.stdout}</pre>"
    else:
        flash('Script not found or access denied.', 'danger')
        return redirect(url_for('user_home'))

# Logout route
@app.route('/logout')
def logout():
    session.pop('username', None)
    flash('You have been logged out.', 'info')
    return redirect(url_for('login'))

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
