import nltk
from nltk.tokenize import WhitespaceTokenizer
from nltk.corpus import stopwords
from string import punctuation
from collections import defaultdict
from pymorphy2 import MorphAnalyzer
from math import log
import json

k1 = 2.0
b = 0.75

def score_BM25(n, fq, N, dl, avdl):
    K = compute_K(dl, avdl)
    IDF = log((N - n + 0.5) / (n + 0.5))
    frac = ((k1 + 1) * fq) / (K + fq)
    return IDF * frac


def compute_K(dl, avdl):
    return k1 * ((1-b) + b * (float(dl)/float(avdl)))


def get_info():
    with open('inv_index.json','r',encoding='utf-8-sig') as f:
        inv_index = json.loads(f.read())
    with open('url_info.json','r',encoding='utf-8-sig') as f:
        url_info = json.loads(f.read())
    return inv_index, url_info


def lemmatisation(text):
    morph = MorphAnalyzer()
    stop_words = stopwords.words('russian')
    exclude = set(punctuation + '0123456789'+'–—'+'«»')
    text = ''.join(ch for ch in text if ch not in exclude)
    tokens = WhitespaceTokenizer().tokenize(text.lower()) 
    tokens = [i.strip() for i in tokens if i not in stop_words]
    lemmas = [morph.parse(i)[0].normal_form for i in tokens]
    return lemmas


def search(query):
    query = lemmatisation(query)
    relevant_urls = defaultdict(float)
    inv_index, url_info = get_info()
    N = len(url_info)
    num_words = sum([i[0] for i in url_info.values()])
    avgdl = num_words/N
    for lemma in query:
        if lemma in inv_index:
            lemma_info = inv_index[lemma]
            n = len(lemma_info)
            for i in lemma_info:
                fq = i[1]
                dl = url_info[i[0]][0]
                #link = 'a href="' + i[0] + '">' + url_info[i[0]][1] + '</a>'
                relevant_urls[(i[0], url_info[i[0]][1])] += score_BM25(n, fq, N, dl, avgdl)
    res = sorted(relevant_urls.items(),key=lambda x: x[1],reverse=True)[:10]
    return res

