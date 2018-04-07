import time, json, requests
import http.client, urllib.request, urllib.parse, urllib.error, base64


def find_text(d, text):
	for k, v in d.items():
		if k == "words":
			text.append('\n')
		if k == "text":
			text.append(v)
		if isinstance(v, dict):
			find_text(v, text)
		elif isinstance(v, list):
			check_list(v, text)


def check_list(v, text):
    for elem in v:
        if isinstance(elem, dict):
            find_text(elem, text)
        elif isinstance(elem, list):
            check_list(elem, text)



def printed(url):
	
	headers = {
        # Request headers.
        'Content-Type': 'application/json',

        # NOTE: Replace the "Ocp-Apim-Subscription-Key" value with a valid subscription key.
        'Ocp-Apim-Subscription-Key': '7d744e6c6624482d87d8b2eb2a77450d',
	}
	
	params = urllib.parse.urlencode({
        # Request parameters. The language setting "unk" means automatically detect the language.
        'language': 'unk',
        'detectOrientation ': 'true',
	})

    # Replace the three dots below with the URL of a JPEG image containing text.
	body = {'url': str(url)}
	body = json.dumps(body, ensure_ascii=False)
	
	try:
        # NOTE: You must use the same location in your REST call as you used to obtain your subscription keys.
        #   For example, if you obtained your subscription keys from westus, replace "westcentralus" in the
        #   URL below with "westus".
		conn = http.client.HTTPSConnection('westcentralus.api.cognitive.microsoft.com')
		conn.request("POST", "/vision/v1.0/ocr?%s" % params, body, headers)
		response = conn.getresponse()
		data = response.read()
		data = json.loads(data.decode())
        #print(data)
		text = []
		find_text(data, text)
		conn.close()
		text = ' '.join(text)
		text = text.split('\n')
		return text
		
	except Exception as e:
		return 'Error:{}'.format(str(e))
		

def written(url):
	requestHeaders = {
        # Request headers.
        # Another valid content type is "application/octet-stream".
        'Content-Type': 'application/json',

        # NOTE: Replace the "Ocp-Apim-Subscription-Key" value with a valid subscription key.
        'Ocp-Apim-Subscription-Key': '7d744e6c6624482d87d8b2eb2a77450d',
	}

    # Replace the three dots below with the URL of a JPEG image containing text.
	body = {'url': str(url)}
    # NOTE: You must use the same location in your REST call as you used to obtain your subscription keys.
    #   For example, if you obtained your subscription keys from westus, replace "westcentralus" in the
    #   URL below with "westus".
	serviceUrl = 'https://westcentralus.api.cognitive.microsoft.com/vision/v1.0/RecognizeText'

    # For printed text, set "handwriting" to false.
	params = {'handwriting': 'true'}

	try:
		response = requests.request('post', serviceUrl, json=body, data=None, headers=requestHeaders, params=params)
		print(response.status_code)

        # This is the URI where you can get the text recognition operation result.
		operationLocation = response.headers['Operation-Location']

        # Note: The response may not be immediately available. Handwriting recognition is an
        # async operation that can take a variable amount of time depending on the length
        # of the text you want to recognize. You may need to wait or retry this GET operation.
		time.sleep(10)
		response = requests.request('get', operationLocation, json=None, data=None, headers=requestHeaders, params=None)
		data = response.json()
		text = []
		find_text(data, text)
		text = ' '.join(text)
		text = text.split('\n')
		return text
	except Exception as e:
		return 'Error:{}'.format(str(e))


def main():
    result = printed('https://s-media-cache-ak0.pinimg.com/736x/ee/4c/15/ee4c15eec262f09a8f6de56fa1a383b6.jpg')
    print(result)


if __name__ == '__main__':
    main()
