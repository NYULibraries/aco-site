const discoveryCore = process.env.DISCOVERY_CORE;

module.exports = exports = {
  "curl" : {
    "featuredTitlesAll" : {
      "src" : `${discoveryCore}/select?wt=json&fq=sm_collection_code:aco&fq=ss_language:en&fq=(ss_book_identifier:columbia_aco003391+OR+ss_book_identifier:nyu_aco000348+OR+ss_book_identifier:princeton_aco000320+OR+ss_book_identifier:aub_aco001663+OR+ss_book_identifier:nyu_aco000227+OR+ss_book_identifier:aub_aco001474+OR+ss_book_identifier:cornell_aco000223+OR+ss_book_identifier:aub_aco000056+OR+ss_book_identifier:cornell_aco000032)&sort=ss_book_identifier%20desc&rows=30&fl=*&hl=falsei&q=*`,
      "dest" : "source/json/datasources/featuredTitlesAll.json"
    },
    "frontCount" : {
      "src" : `${discoveryCore}/select?wt=json&fq=sm_collection_code:aco&fq=ss_language:en&fl=id&hl=false&q=*`,
      "dest" : "source/json/datasources/frontCountId.json"
    },
    "subjectCount" : {
      "src" : `${discoveryCore}/select?wt=json&json.nl=arrmap&fq=sm_collection_code:aco&rows=0&facet=true&facet.limit=-1&facet.mincount=1&facet.field=im_field_subject&hl=falsei&q=*`,
      "dest" : "source/json/datasources/subjectCount.json"
    },
    "categoryQueryEn" : {
      "src" : `${discoveryCore}/select?wt=json&json.nl=arrmap&q=sm_collection_code:aco&fq=sm_topic:*&facet=true&rows=0&facet.field=sm_topic`,
      "dest" : "source/json/datasources/categoryQueryEn.json"
    },
    "categoryQueryAr" : {
      "src" : `${discoveryCore}/select?wt=json&json.nl=arrmap&q=sm_collection_code:aco&fq=sm_ar_topic:*&facet=true&rows=0&facet.field=sm_ar_topic`,
      "dest" : "source/json/datasources/categoryQueryAr.json"
    }
  }
}
