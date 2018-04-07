class UniqObject:

    _instance = None
        
    @classmethod
    def create_object(cls):
        if cls._instance == None:
            cls._instance = UniqObject()
        return cls._instance
