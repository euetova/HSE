import datetime

from flask import Flask
from flask import url_for, render_template, request, redirect

app = Flask(__name__)

likes = {}
users = {}


@app.route('/')
def index():
    return render_template('index.html')


@app.route('/result')
def result():
	global likes
	global users
	if request.args:
		name = request.args['name']
		pet = request.args['pets']
	if name not in users:
		users[name] = 1
	else:
		users[name] += 1
	if pet not in likes:
		likes[pet] = 1
	else:
		likes[pet] += 1
	return render_template('result.html', users=users, likes=likes)

if __name__ == '__main__':
    app.run(debug=True)
