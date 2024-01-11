import com.example.spring_rest_api.spring_rest_api.service.CloudVendorService;
import java.util.List;

import org.springframework.stereotype.Service;

@Service
public class CloudVendorServiceImpl implements CloudVendorApiService{
    //Instance of repository layer because service interacts with the repository
    CloudVendorRepository cloudvendorRepository;

    public CloudVendorServiceImpl(CloudVendorRepository cloudvendorRepository){
        this.cloudvendorRepository = cloudvendorRepository;
    }
    @Override
    public String createCloudVendor(CloudVendor cloudvendor){
        //save to db
        cloudvendorRepository.save(cloudvendor);
        return "Success";
    }

    @Override
    public String updateCloudVendor(CloudVendor cloudvendor){
        //more bussiness logic could go here
        cloudvendorRepository.save(cloudvendor);
        return "Success";        
    }

    @Override
    public CloudVendor deleteCloudVendor(String cloudvendorId){
        return cloudvendorRepository.deleteById(cloudvendorId);  
        return "Success";  
    }

    @Override
    public List<CloudVendor> getAllCloudVendors(){
        return  cloudvendorRepository.findAll();
    }

}