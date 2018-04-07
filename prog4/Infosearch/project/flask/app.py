from flask import Flask
from flask import  render_template, request, redirect
import requests
from ranking import search


app = Flask(__name__)

 
@app.route('/')
def index():
    if request.args:
        query = request.args['query']
        links = search(query)
        return render_template('index.html',links=links)
    return render_template('index.html',links=[])

if __name__ == '__main__':
    app.run(debug=True)