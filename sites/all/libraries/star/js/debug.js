function debug(obj) {
	alert((typeof obj) + " " + JSON.stringify(obj));
}

function debug_r(arr) {
	var str = '';
	for (var el in arr)	{
		if (arr.hasOwnProperty(el)) {
			str += el + " => (" + typeof(arr[el]) + ") " + arr[el] + "\n";
		}
	}
	alert(str);
}

function listFunctions(obj) {
  var str = '';
  for (var el in obj) {
    if (typeof obj[el] == 'function') {
      str += el + "\n";
    }
  }
  alert(str);
}

// @todo add addslashes() to case 'string' 
//function serialize(obj, indent, objects) {
//  if (indent === undefined) {
//    indent = '';
//  }
//  if (objects === undefined) {
//    objects = [];
//  }
//	switch (typeof obj) {
//		case 'string':
//			return "'" + obj + "'";
//		case 'number':
//		case 'boolean':
//			return String(obj);
//		case 'object':
//			if (obj === null) {
//				return 'null';
//			}
//			// check if we already examined this object:
//			if (in_array(obj, objects)) {
//        return 'CIRC-REF';
//			}
//			// remember this object:
//			objects[objects.length] = obj;
//			// create a JSON string of the object:
//			var result = "{";
//			for (var key in obj) {
//				if (obj.hasOwnProperty(key) && typeof obj[key] != 'function') {
//					if (result != "{") {
//						result += ",\n" + indent;
//					}
//					result += key + ": " + serialize(obj[key], indent + '  ', objects);
//				}
//			}
//			result += "}";
//			return result;
//		default:
//			return typeof obj;
//	}
//}
