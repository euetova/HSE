from flask import Flask
from flask import url_for, render_template, request, redirect
from pymystem3 import Mystem
from collections import Counter, defaultdict, OrderedDict
import json
import requests
import http.client, urllib.request, urllib.parse, urllib.error, base64
from ocr import printed, written

m = Mystem()
app = Flask(__name__)


def verbes(text):
	dict_v = defaultdict(int)
	dict_w = defaultdict(int)
	ana = m.analyze(text)
	lemmas = []
	for i in ana:
		if i['text'].strip() and 'analysis' in i and i['analysis']:
			dict_w['слова'] += 1
			gr = i['analysis'][0]['gr']
			if 'V' == gr.split('=')[0].split(',')[0]:
				dict_w['глаголы'] += 1
				if 'несов' in gr:
					dict_v['несовершенные'] += 1
				if 'нп' in gr:
					dict_v['непереходные'] += 1
				lemmas.append(i['analysis'][0]['lex'])
	dict_v['совершенные'] = dict_w['глаголы'] - dict_v['несовершенные']
	dict_v['переходные'] = dict_w['глаголы'] - dict_v['непереходные']
	dict_w['част'] = dict_w['глаголы']/dict_w['слова']
	lemmas = Counter(lemmas)
	od = OrderedDict(sorted(lemmas.items(), key=lambda t: (t[1], t[0]), reverse=True))
	return dict_w, dict_v, od

	
def vk_api(method, **kwargs):
    api_request = 'https://api.vk.com/method/'+method + '?'
    api_request += '&'.join(['{}={}'.format(key, kwargs[key]) for key in kwargs])
    return json.loads(requests.get(api_request).text)

	
def get_members(group):
    users = []
    result = vk_api('groups.getMembers', group_id=group)
    members_count = result['response']['count']
    users += result['response']["users"]
    while len(users) < members_count:
        result = vk_api('groups.getMembers', group_id=group, offset=len(users))
        users += result['response']["users"]
    return set(users)


def get_info(group1, group2):
	dict_vk = {}
	d = {}
	gr1 = vk_api(method='groups.getById', group_id=group1)['response'][0]['is_closed']
	gr2 = vk_api(method='groups.getById', group_id=group2)['response'][0]['is_closed']
	if gr1 == 1 or gr2 == 1:
		dict_vk['closed'] = True
		dict_vk[group1] = 0
		dict_vk[group2] = 0
		dict_vk['intersection'] = 0
	else:
		dict_vk['closed'] = False
		dict_vk[group1] = get_members(group1)
		dict_vk[group2] = get_members(group2)
		dict_vk['intersection'] = len(dict_vk[group1].intersection(dict_vk[group2]))
		dict_vk[group1] = len(dict_vk[group1])
		dict_vk[group2] = len(dict_vk[group2])
	d['intersection'] = dict_vk['intersection']
	d[group1] = dict_vk[group1] - d['intersection']
	d[group2] = dict_vk[group2] - d['intersection']
	return dict_vk, d
	

@app.route('/', methods=['get'])
def index():
    return render_template('index.html')
	
	
@app.route('/verbes', methods=['get', 'post'])
def verbes_http():
    if request.form:
        text = request.form['text']
        dict_w, dict_v, lemmas = verbes(text)
        return render_template('verbes.html', input=text, dict_v=dict_v, lemmas=lemmas, dict_w=dict_w)
    return render_template('verbes.html', dict_v={})

	
@app.route('/vk', methods=['get', 'post'])
def vk():
    if request.form:
        group1 = request.form['group1']
        group2 = request.form['group2']
        dict_vk, d = get_info(group1, group2)
        return render_template('vk.html', dict_vk=dict_vk, d=d, group1=group1, group2=group2)
    return render_template('vk.html', d={})
	
@app.route('/compvis', methods=['get', 'post'])
def compvision():
	if request.form:
		url = request.form['img']
		if request.form['option'] == 'written':
			text = written(url)
			return render_template('compvis.html', url=url, text=text)
		else:
			text = printed(url)
			return render_template('compvis.html', url=url, text=text)
	return render_template('compvis.html')

if __name__ == '__main__':
    app.run(debug=True)