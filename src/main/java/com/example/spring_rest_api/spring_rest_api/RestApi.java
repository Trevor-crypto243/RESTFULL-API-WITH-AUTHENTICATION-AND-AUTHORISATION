package com.example.spring_rest_api.spring_rest_api;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.bind.annotation.RequestParam;


@RestController
public class RestApi {

    @GetMapping("/rhodah-mwose")
    public String first_api(){
        return "Hello Trevor, I am Rhodah Mwose";
    }

    @GetMapping("/trevor")
    public String reply() {
        return "Hello rhodah, I miss you, I can even die for you.....";
    }
    
    
}
