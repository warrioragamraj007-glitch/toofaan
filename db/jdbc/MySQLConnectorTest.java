import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class MySQLConnectorTest {
    public static void main(String[] args) {
        // MySQL connection details
        String url = "jdbc:mysql://localhost:3306/fs"; // Replace with your database name
        String user = "fsuser"; // Replace with your MySQL username
        String password = "Kmit123$"; // Replace with your MySQL password

        Connection connection = null;

        try {
            // Load MySQL JDBC driver
            Class.forName("com.mysql.cj.jdbc.Driver");

            // Create a connection to the MySQL database
            connection = DriverManager.getConnection(url, user, password);

            // Check if the connection is successful
            if (connection != null) {
                System.out.println("Successfully connected to the MySQL database!");
            }

        } catch (SQLException e) {
            System.out.println("SQL Exception: " + e.getMessage());
        } catch (ClassNotFoundException e) {
            System.out.println("Class Not Found Exception: " + e.getMessage());
        } finally {
            // Close the connection if it was established
            if (connection != null) {
                try {
                    connection.close();
                } catch (SQLException e) {
                    e.printStackTrace();
                }
            }
        }
    }
}
