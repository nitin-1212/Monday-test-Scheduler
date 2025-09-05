from flask import Flask, request, jsonify, render_template, send_file
from flask_cors import CORS
import mysql.connector
import bcrypt
import datetime
import csv
import io

# Database connection
def connect_db():
    return mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='testdb'
    )

app = Flask(__name__)
CORS(app, resources={r"/api/*": {"origins": "*"}})

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/login')
def login_page():
    return render_template('login.html')

@app.route('/register')
def register_page():
    return render_template('register.html')

@app.route('/student')
def student_page():
    return render_template('student.html')

@app.route('/admin-dashboard')
def serve_admin_dashboard():
    return render_template('admin1.html')

# ---------------------------
# API ROUTES
# ---------------------------

# ✅ Health Check
@app.route('/api/health', methods=['GET'])
def health_check():
    return jsonify({'status': 'healthy'}), 200

# ✅ Get all students with activity info
@app.route('/api/students', methods=['GET'])
def get_students():
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)

    cursor.execute("SELECT id, username, email, fullname, phone FROM students")
    students = cursor.fetchall()

    # For demo, mark login & attempt randomly (replace with real tracking later)
    for s in students:
        s['logged_in'] = False   # TODO: replace with real session tracking
        s['attempted_exam'] = False
    cursor.close()
    conn.close()
    return jsonify(students), 200

# ✅ Register new student
@app.route('/api/register', methods=['POST'])
def register_student():
    conn = connect_db()
    cursor = conn.cursor()
    try:
        data = request.get_json()
        required_fields = ['username', 'password', 'email', 'fullName', 'phone']
        if not all(field in data for field in required_fields):
            return jsonify({'error': 'All fields are required'}), 400

        hashed_password = bcrypt.hashpw(data['password'].encode('utf-8'), bcrypt.gensalt()).decode('utf-8')

        cursor.execute("""
            INSERT INTO students (username, password, email, fullname, phone)
            VALUES (%s, %s, %s, %s, %s)
        """, (data['username'], hashed_password, data['email'], data['fullName'], data['phone']))
        conn.commit()
        return jsonify({'message': 'Registration successful'}), 201
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    finally:
        cursor.close()
        conn.close()

# ✅ Login (admin or student)
@app.route('/api/login', methods=['POST'])
def login():
    conn = None
    cursor = None
    try:
        data = request.get_json()
        username = data.get('username')
        password = data.get('password')
        role = data.get('role')

        if not all([username, password, role]):
            return jsonify({'error': 'Missing credentials'}), 400

        # Admin check
        if role == 'admin':
            if username == 'admin' and password == 'admin123':
                return jsonify({'success': True, 'role': 'admin'})
            return jsonify({'success': False, 'message': 'Invalid admin credentials'}), 401

        # Student check
        if role == 'student':
            conn = connect_db()
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT * FROM students WHERE username = %s", (username,))
            student = cursor.fetchone()
            if not student:
                return jsonify({'success': False, 'message': 'No student found'}), 404

            if bcrypt.checkpw(password.encode('utf-8'), student['password'].encode('utf-8')):
                return jsonify({'success': True, 'role': 'student'})
            return jsonify({'success': False, 'message': 'Invalid password'}), 401

        return jsonify({'error': 'Invalid role'}), 400
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    finally:
        if cursor: cursor.close()
        if conn and conn.is_connected(): conn.close()

# ✅ Create new exam
@app.route('/api/exams', methods=['POST'])
def create_exam():
    try:
        data = request.get_json()
        title = data.get('title')
        description = data.get('description')
        start_time_str = data.get('start_time')
        duration_minutes = data.get('duration') or data.get('duration_minutes')

        if not all([title, start_time_str, duration_minutes]):
            return jsonify({'error': 'Missing required fields'}), 400

        # Convert datetime-local to MySQL format safely
        try:
            start_time = datetime.datetime.fromisoformat(start_time_str)
        except ValueError:
            return jsonify({'error': 'Invalid datetime format'}), 400

        conn = connect_db()
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO exams (title, description, start_time, duration_minutes)
            VALUES (%s, %s, %s, %s)
        """, (title, description, start_time, duration_minutes))
        conn.commit()
        exam_id = cursor.lastrowid   # ✅ get the new exam ID
        cursor.close()
        conn.close()

        return jsonify({'message': 'Exam created successfully', 'exam_id': exam_id}), 201
    except Exception as e:
        import traceback; traceback.print_exc()
        return jsonify({'error': str(e)}), 500


@app.route('/api/exams', methods=['GET'])
def get_exams():
    try:
        conn = connect_db()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, title, description, start_time, duration_minutes FROM exams ORDER BY start_time ASC")
        exams = cursor.fetchall()
        cursor.close()
        conn.close()

        # Convert datetime to string
        for e in exams:
            e['start_time'] = e['start_time'].strftime("%Y-%m-%dT%H:%M")

        return jsonify(exams), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    
@app.route('/exam/<int:exam_id>')
def exam_page(exam_id):
    return render_template('exam.html', exam_id=exam_id)



# ✅ Add question to exam
@app.route('/api/questions', methods=['POST'])
def add_question():
    try:
        data = request.get_json()
        exam_id = data.get('exam_id')
        question_text = data.get('question_text')
        option_a = data.get('option_a')
        option_b = data.get('option_b')
        option_c = data.get('option_c')
        option_d = data.get('option_d')
        correct_option = data.get('correct_option')

        if not all([exam_id, question_text, option_a, option_b, option_c, option_d, correct_option]):
            return jsonify({'error': 'All fields are required'}), 400

        conn = connect_db()
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """, (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option))
        conn.commit()
        cursor.close()
        conn.close()

        return jsonify({'message': 'Question added successfully'}), 201
    except Exception as e:
        import traceback; traceback.print_exc()
        return jsonify({'error': str(e)}), 500



# ✅ Export student activity data
@app.route('/api/export', methods=['GET'])
def export_data():
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT id, username, email, fullname, phone FROM students")
    students = cursor.fetchall()
    cursor.close()
    conn.close()

    output = io.StringIO()
    writer = csv.writer(output)
    writer.writerow(['ID', 'Username', 'Email', 'Full Name', 'Phone'])
    for s in students:
        writer.writerow([s['id'], s['username'], s['email'], s['fullname'], s['phone']])
    output.seek(0)

    return send_file(
        io.BytesIO(output.getvalue().encode()),
        mimetype='text/csv',
        as_attachment=True,
        download_name='students.csv'
    )

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
