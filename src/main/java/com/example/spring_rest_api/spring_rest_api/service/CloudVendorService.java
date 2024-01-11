import com.example.spring_rest_api.spring_rest_api.model.CloudVendor;
import java.util.List;
public interface CloudVendorService {
    public String createCloudVendor(CloudVendor cloudVendor);  
    public String updateCloudVendor(CloudVendor cloudVendor);
    public String deleteCloudVendor(String  cloudVendorId);
    public CloudVendor getCloudVendor(String cloudVerId);
    public List<CloudVendor> getAllCloudVendors();
}
