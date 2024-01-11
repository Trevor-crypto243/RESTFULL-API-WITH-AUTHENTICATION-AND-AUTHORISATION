import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RestController;
import com.example.spring_rest_api.spring_rest_api.model.CloudVendor;

@RestController
@RequestMapping("/cloudvendor")
public class CloudVendorController {
    //add instance of service layer because controller interacts with the service layer
    CloudVendorService cloudVendorService;

    //constructor of cloud vendor controller
    public CloudVendorController(CloudVendorService cloudVendorService){
        this.cloudVendorService = cloudVendorService;
    }

    //Read specific cloud vendor details
    @GetMapping("{vendorId}")
    public CloudVendor getCloudVendorDetails(@PathVariable("vendorId") String vendorId) {
        //getting cloud venor from cloud vendor service
        return cloudVendorService.getCloudVendor(vendorId);
    }

    //Read all cloud vendor vendors from DB
    @GetMapping
    public List<CloudVendor> getAllCloudVendorDetails() {
        //getting cloud venor from cloud vendor service
        return cloudVendorService.getAllCloudVendor();
    }

    @PostMapping
    public String createCloudVendorDetails(@RequestBody CloudVendor cloudVendor) {
        cloudVendorService.createCloudVendor(cloudVendor);
        return "Cloud Vendor Created Succesfully";
    }

    @PutMapping //for updating
    public String updateCloudVendorDetails(@RequestBody CloudVendor cloudVendor) {
        cloudVendorService.updateCloudVendor(cloudVendor);
        return "Cloud Vendor updated Succesfully";
    }

    @DeleteMapping("{vendorId}")
    public String updateCloudVendorDetails(@PathVariable("vendorId") String vendorId) {
        cloudVendorService.deleteCloudVendor(vendorId);
        return "Cloud Vendor deleted Succesfully";
    }
        
}
