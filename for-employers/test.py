import importlib.util
import sys

path = "C:/Users/AMIZING/Desktop/config.py"
module_name = "config"

spec = importlib.util.spec_from_file_location(module_name, path)
config = importlib.util.module_from_spec(spec)
sys.modules[module_name] = config
spec.loader.exec_module(config)

print(config.my_api_key)
