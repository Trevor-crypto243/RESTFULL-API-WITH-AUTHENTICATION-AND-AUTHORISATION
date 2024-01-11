import org.springframework.data.jpa.repository.JpaRepository;
import com.example.spring_rest_api.spring_rest_api.model.CloudVendor;

//instance of the model because the repository interacts with the database
public interface CloudVendorRepository extends JpaRepository<CloudVendor, String>{//the model and the type of the primary key
    //provides already defined methods

}
